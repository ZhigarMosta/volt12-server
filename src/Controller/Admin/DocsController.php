<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DocsController extends AbstractController
{
    #[Route('/admin/docs', name: 'app_admin_docs', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('admin/docs/index.html.twig');
    }
}
