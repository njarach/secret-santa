<?php

namespace App\Controller;

use App\Entity\Event;
use App\EntityServices\EventService;
use App\Form\EventType;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private EventService $eventService;

    public function __construct(EventService $eventService){
        $this->eventService = $eventService;
    }

    /**
     * @throws RandomException
     */
    #[Route('/home', name: 'app_home')]
    public function index(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->eventService->setEventData($event);
            $this->addFlash('success', 'Event created!');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
