<?php

namespace App\Controller\Admin;

use App\Entity\UserOrder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user-orders')]
class UserOrderStatusController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/{id}/change-status', name: 'app_admin_user_order_change_status', methods: ['POST'])]
    public function changeStatus(UserOrder $userOrder, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? '';

        if (!array_key_exists($status, UserOrder::STATUSES)) {
            return $this->json(['success' => false, 'error' => 'Некорректный статус'], 400);
        }

        $userOrder->setStatus($status);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'status' => $status,
            'label' => UserOrder::STATUSES[$status],
        ]);
    }

    #[Route('/{id}/set-processing', name: 'app_admin_user_order_set_processing', methods: ['GET'])]
    public function setProcessing(UserOrder $userOrder): RedirectResponse
    {
        $userOrder->setStatus(UserOrder::STATUS_PROCESSING);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_user_order_index', [
            'criteria' => ['id' => $userOrder->getId()],
        ]);
    }
}
