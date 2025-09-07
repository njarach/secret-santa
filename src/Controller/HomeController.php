<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        return $this->render('home/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
