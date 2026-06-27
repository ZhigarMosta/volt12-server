<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Кастомный дашборд админки. В отличие от стандартного Sylius-контроллера
 * не требует наличия канала (приложение каналы не использует).
 */
class DashboardController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('admin/dashboard/index.html.twig', [
            'channel_code' => null,
        ]);
    }
}
