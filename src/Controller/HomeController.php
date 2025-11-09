<?php

namespace App\Controller;

use App\Form\EventLoginType;
use App\Repository\ParticipantRepository;
use App\Security\EventAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private ParticipantRepository $participantRepository;
    private EventAccessService $eventAccessService;

    public function __construct(
        ParticipantRepository $participantRepository,
        EventAccessService $eventAccessService
    ) {
        $this->participantRepository = $participantRepository;
        $this->eventAccessService = $eventAccessService;
    }

    #[Route('/home', name: 'app_home')]
    public function index(Request $request): Response
    {
        $loginForm = $this->createForm(EventLoginType::class);
        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $data = $loginForm->getData();
            $email = $data['email'];
            $token = $data['token'];

            // Chercher le participant par email et token
            $participant = $this->participantRepository->findOneBy([
                'email' => $email,
                'eventAccessToken' => $token,
            ]);

            if (!$participant) {
                $this->addFlash('error', 'Email ou token incorrect. Veuillez vérifier vos informations.');
                return $this->redirectToRoute('app_home');
            }

            // Vérifier que le participant est vérifié
            if (!$participant->isVerified()) {
                $this->addFlash('error', 'Votre compte n\'a pas encore été vérifié. Veuillez cliquer sur le lien dans votre email de bienvenue.');
                return $this->redirectToRoute('app_home');
            }

            // Authentifier l'utilisateur
            $event = $participant->getEvent();
            $authentication = $this->eventAccessService->authenticateUser($event->getId(), $token);

            if ($authentication === 'participant') {
                return $this->redirectToRoute('app_event_participant_dashboard', ['id' => $event->getId()]);
            } elseif ($authentication === 'admin') {
                return $this->redirectToRoute('app_event_admin_dashboard', ['id' => $event->getId()]);
            }

            $this->addFlash('error', 'Une erreur s\'est produite lors de la connexion.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'loginForm' => $loginForm->createView(),
        ]);
    }
}
