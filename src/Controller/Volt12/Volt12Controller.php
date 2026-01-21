<?php

namespace App\Controller\Volt12;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class Volt12Controller
{
    #[Route('/volt12/hello', name: 'volt12_hello', methods: ['GET'])]
    public function hello(): Response
    {
        return new Response('hello world', 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
