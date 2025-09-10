<?php

namespace App\Controller;

use App\Entity\Event;
use App\EntityServices\EventService;
use App\Form\EventType;
use App\Mailer\EventVerificationMailer;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class EventCrudController extends AbstractController
{
    private EventService $eventService;
    public function __construct(EventService $eventService) {
        $this->eventService = $eventService;
    }

    /**
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    #[Route('/new-event', name: 'app_new_event')]
    public function new(Request $request, EventVerificationMailer $eventVerificationMailer): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventService->setEventData($event);
            try {
                $eventVerificationMailer->sendEventVerificationEmail($event);
            } catch (TransportExceptionInterface|LoaderError|RuntimeError|SyntaxError $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('app_new_event');
            }
            $this->addFlash('success', 'Event created!');

            /* TODO : intégrer une page d'attente de vérification d'un mail */
            /* TODO : gérer les pages d'erreur */
            return $this->render('event/pending_verification.html.twig', ['event' => $event]);
        }
        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
