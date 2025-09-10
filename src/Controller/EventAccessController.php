<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventAccessController extends AbstractController
{
    #[Route('/event/access/{id}/{token}', name: 'app_event_access')]
    public function index(int $id, string $token): Response
    {
        /* TODO : this will be used to redirect user to the page they need depending on the link they used (found in their access mail) */


        return $this->render('event_access/index.html.twig', [
            'controller_name' => 'EventAccessController',
        ]);
    }
}
