<?php

namespace App\Controller;

use App\Entity\Event;
use App\Services\EventService;
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

    public function __construct(EventService $eventService)
    {
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
                $this->addFlash('success', "L'évènement a été créé avec succès.");
            } catch (TransportExceptionInterface|LoaderError|RuntimeError|SyntaxError) {
                $this->addFlash('danger', "L'envoi du mail de vérification a échoué.");
                return $this->redirectToRoute('app_new_event');
            }
            return $this->render('event/pending_verification.html.twig', ['event' => $event]);
        }

        return $this->render('event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // TODO : routes pour updater ou supprimer un évènement. Peut être voir pour un 'super admin' controller qui permet à un super admin, l'administrateur du site, de voir
    // la liste de tous les secret santa pour les bloquer, supprimer, voir les dates...
}
