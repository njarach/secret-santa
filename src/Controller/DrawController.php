<?php

namespace App\Controller;

use App\Mailer\DrawResultMailer;
use App\Repository\EventRepository;
use App\Security\Voter\AccessVoter;
use App\Services\DrawService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class DrawController extends AbstractController
{
    public function __construct(
        private readonly DrawService $drawService,
        private readonly EventRepository $eventRepository,
        private readonly DrawResultMailer $drawResultMailer,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/event/{id}/perform-draw', name: 'app_event_perform_draw', methods: ['POST'])]
    public function performDraw(int $id): Response
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException("L'événement n'existe pas.");
        }

        $this->denyAccessUnlessGranted(AccessVoter::ADMIN_ACCESS, $event);

        try {
            $this->drawService->performDraw($event);

            foreach ($event->getParticipants() as $participant) {
                if ($participant->getDraw()) {
                    $this->drawResultMailer->sendDrawResult($participant, $event);
                }
            }

            $this->addFlash('success', 'Le tirage au sort a été effectué avec succès ! Les participants ont reçu leurs résultats par email.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_event_admin_dashboard', ['id' => $id]);
    }

    #[Route('/event/{id}/reveal-draws', name: 'app_event_reveal_draws')]
    public function revealDraws(int $id): Response
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException("L'événement n'existe pas.");
        }

        $this->denyAccessUnlessGranted(AccessVoter::ADMIN_ACCESS, $event);

        $draws = $this->drawService->getDrawsForEvent($event);

        return $this->render('event/admin/reveal_draws.html.twig', [
            'event' => $event,
            'draws' => $draws,
        ]);
    }
}
