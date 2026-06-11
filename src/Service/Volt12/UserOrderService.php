<?php

namespace App\Service\Volt12;

use App\Entity\User;
use App\Entity\UserOrder;
use App\Entity\UserOrderItem;
use App\Repository\CatalogItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Volt12\FeedbackService;

class UserOrderService
{
    public function __construct(
        private CatalogItemRepository $catalogItemRepository,
        private EntityManagerInterface $entityManager,
        private FeedbackService $feedbackService
    ) {}

    public function create(array $data, ?User $user): UserOrder
    {
        $order = new UserOrder();
        $order->setUser($user);
        $order->setStatus('new');

        $order->setFirstName(trim($data['first_name'] ?? ''));
        $order->setLastName(trim($data['last_name'] ?? ''));
        $order->setPhone(trim($data['phone'] ?? ''));
        $order->setEmail(trim($data['email'] ?? ''));
        $order->setStreet(trim($data['street'] ?? '') ?: null);
        $order->setHouse(trim($data['house'] ?? '') ?: null);
        $order->setEntrance(trim($data['entrance'] ?? '') ?: null);
        $order->setApartment(trim($data['apartment'] ?? '') ?: null);
        $order->setCity(trim($data['city'] ?? ''));
        $order->setRegion(trim($data['region'] ?? ''));
        $order->setPostalCode(trim($data['postal_code'] ?? ''));
        $order->setComment(trim($data['comment'] ?? '') ?: null);

        $items = $data['items'] ?? [];
        $totalPrice = 0;

        foreach ($items as $itemData) {
            $catalogItemId = (int) ($itemData['catalog_item_id'] ?? 0);
            $quantity = max(1, (int) ($itemData['quantity'] ?? 1));

            if ($catalogItemId <= 0) {
                continue;
            }

            $catalogItem = $this->catalogItemRepository->find($catalogItemId);
            if (!$catalogItem) {
                continue;
            }

            $price = $catalogItem->getPrice();
            $itemTotal = $price * $quantity;

            $orderItem = new UserOrderItem();
            $orderItem->setCatalogItem($catalogItem);
            $orderItem->setName($catalogItem->getName());
            $orderItem->setPrice($price);
            $orderItem->setQuantity($quantity);
            $orderItem->setTotalPrice($itemTotal);

            $order->addItem($orderItem);
            $totalPrice += $itemTotal;
        }

        $order->setTotalPrice($totalPrice);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

//        $this->feedbackService->sendOrderConfirmation($order->getEmail(), $this->serializeOrder($order));

        return $order;
    }

    private function serializeOrder(UserOrder $order): array
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $items[] = [
                'name'        => $item->getName(),
                'quantity'    => $item->getQuantity(),
                'price'       => $item->getPrice(),
                'total_price' => $item->getTotalPrice(),
            ];
        }

        return [
            'id'          => $order->getId(),
            'first_name'  => $order->getFirstName(),
            'last_name'   => $order->getLastName(),
            'phone'       => $order->getPhone(),
            'email'       => $order->getEmail(),
            'street'      => $order->getStreet(),
            'house'       => $order->getHouse(),
            'entrance'    => $order->getEntrance(),
            'apartment'   => $order->getApartment(),
            'city'        => $order->getCity(),
            'region'      => $order->getRegion(),
            'postal_code' => $order->getPostalCode(),
            'comment'     => $order->getComment(),
            'total_price' => $order->getTotalPrice(),
            'items'       => $items,
        ];
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (empty(trim($data['first_name'] ?? ''))) $errors[] = 'first_name is required';
        if (empty(trim($data['last_name'] ?? '')))  $errors[] = 'last_name is required';
        if (empty(trim($data['phone'] ?? '')))       $errors[] = 'phone is required';
        if (empty(trim($data['email'] ?? '')))       $errors[] = 'email is required';
        if (empty(trim($data['city'] ?? '')))        $errors[] = 'city is required';
        if (empty(trim($data['region'] ?? '')))      $errors[] = 'region is required';
        if (empty(trim($data['postal_code'] ?? ''))) $errors[] = 'postal_code is required';

        if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            $errors[] = 'items is required and must not be empty';
        }

        return $errors;
    }
}
