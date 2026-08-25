<?php

namespace App\Service\Volt12;

use App\Entity\CatalogItem;
use App\Entity\User;
use App\Entity\UserOrder;
use App\Entity\UserOrderItem;
use App\Provider\ProductCodeProvider;
use App\Repository\CatalogItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Заказ «в один клик» со страницы товара: один товар, минимум контактов,
 * без адреса и без прохождения оформления. Пишется в те же user_orders,
 * что и обычный заказ, но с source = quick.
 */
class QuickOrderService
{
    public const MAX_QUANTITY = 99;
    private const MAX_NAME_LENGTH = 255;
    private const MAX_COMMENT_LENGTH = 1000;

    public function __construct(
        private CatalogItemRepository $catalogItemRepository,
        private UserOrderService $userOrderService,
        private FeedbackService $feedbackService,
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * Проверка полей окна. Возвращает список строк-ошибок в том же формате,
     * что /volt12/feedback и /volt12/booking — фронт по ним подсвечивает поля.
     *
     * @return string[]
     */
    public function validate(array $data): array
    {
        $errors = [];

        if ((int) ($data['catalog_item_id'] ?? 0) <= 0) {
            $errors[] = 'catalog_item_id is required';
        }

        if (array_key_exists('quantity', $data) && $data['quantity'] !== null && $data['quantity'] !== '') {
            $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
            if ($quantity === false || $quantity < 1 || $quantity > self::MAX_QUANTITY) {
                $errors[] = 'quantity is invalid';
            }
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'name is required';
        } elseif (mb_strlen($name) > self::MAX_NAME_LENGTH) {
            $errors[] = 'name is invalid';
        }

        $phone = trim((string) ($data['phone'] ?? ''));
        if ($phone === '') {
            $errors[] = 'phone is required';
        } elseif ($this->normalizePhone($phone) === null) {
            $errors[] = 'phone is invalid';
        }

        $email = trim((string) ($data['email'] ?? ''));
        if ($email === '') {
            $errors[] = 'email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'email is invalid';
        }

        $comment = trim((string) ($data['comment'] ?? ''));
        if (mb_strlen($comment) > self::MAX_COMMENT_LENGTH) {
            $errors[] = 'comment is invalid';
        }

        if (($data['agree_policy'] ?? false) !== true) {
            $errors[] = 'agree_policy is required';
        }

        return $errors;
    }

    /**
     * Товар для заказа: только опубликованный и с подходящим кодом продукта —
     * те же правила, что на витрине. Null, если такого товара нет.
     */
    public function findOrderableItem(int $catalogItemId): ?CatalogItem
    {
        $items = $this->catalogItemRepository->findByIds(
            [$catalogItemId],
            [ProductCodeProvider::CODE_VOLT12, ProductCodeProvider::CODE_ANY]
        );

        return $items[0] ?? null;
    }

    /**
     * Корзину не трогаем: «в один клик» — сценарий в обход корзины, товар
     * из неё не исчезает и туда не добавляется.
     */
    public function create(array $data, CatalogItem $item, ?User $user): UserOrder
    {
        $quantity = (int) ($data['quantity'] ?? 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        $price = $item->getPrice();
        $itemTotal = $price * $quantity;

        $orderItem = new UserOrderItem();
        $orderItem->setCatalogItem($item);
        $orderItem->setName($item->getName());
        $orderItem->setPrice($price);
        $orderItem->setQuantity($quantity);
        $orderItem->setTotalPrice($itemTotal);

        $order = new UserOrder();
        $order->setUser($user);
        $order->setStatus(UserOrder::STATUS_NEW);
        $order->setSource(UserOrder::SOURCE_QUICK);
        $order->setFirstName(trim((string) ($data['name'] ?? '')));
        $order->setPhone($this->normalizePhone(trim((string) ($data['phone'] ?? ''))) ?? '');
        $order->setEmail(trim((string) ($data['email'] ?? '')));
        $order->setComment(trim((string) ($data['comment'] ?? '')) ?: null);
        $order->addItem($orderItem);
        $order->setTotalPrice($itemTotal);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    /**
     * Письма менеджеру и покупателю. Best effort: заказ уже сохранён и виден
     * в админке, поэтому сбой почты не должен превращаться в ошибку для клиента.
     */
    public function notify(UserOrder $order): void
    {
        try {
            $this->feedbackService->sendOrderConfirmation(
                $order->getEmail(),
                $this->userOrderService->serializeOrderFull($order)
            );
        } catch (\Throwable $e) {
            $this->logger?->error('Не удалось отправить письма по быстрому заказу', [
                'order_id' => $order->getId(),
                'exception' => $e,
            ]);
        }
    }

    /**
     * Телефон к виду +7XXXXXXXXXX. Null, если это не похоже на российский номер.
     */
    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (strlen($digits) !== 11 || !in_array($digits[0], ['7', '8'], true)) {
            return null;
        }

        return '+7' . substr($digits, 1);
    }
}
