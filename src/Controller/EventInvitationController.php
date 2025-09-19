<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventInvitationController extends AbstractController
{
    #[Route('/event/invitation', name: 'app_event_invitation')]
    public function index(): Response
    {

    }

    #[Route('/event/{id}/{token}/join-event', name: 'app_event_join_event')]
    public function joinEvent(int $id, string $token): Response
    {
        return new Response('Join event page, accessed with the invite sent by the event creator. New participant fills form and submitting confirms participation, redirected to user dashboard.', Response::HTTP_OK);
    }
}
