<?php

namespace App\Tests\Controller;

use Symfony\Component\Mailer\MailerInterface;

class EventCrudControllerTest extends BaseControllerTest
{
    public function testNewEventFormIsDisplayed(): void
    {
        $this->client->request('GET', '/new-event');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Créer un nouveau Secret Santa');
    }

    public function testCreateNewEvent(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock->expects($this->once())->method('send');

        static::getContainer()->set(MailerInterface::class, $mailerMock);

        $crawler = $this->client->request('GET', '/new-event');

        $form = $crawler->selectButton('Créer!')->form([
            'event[adminEmail]' => 'test@example.com',
            'event[name]' => 'Mon Secret Santa',
            'event[description]' => 'Un super événement',
            'event[budget]' => '25.50',
        ]);

        $this->client->submit($form);

        $event = $this->em->getRepository(\App\Entity\Event::class)
            ->findOneBy(['adminEmail' => 'test@example.com']);

        $this->assertNotNull($event);
        $this->assertEquals('Mon Secret Santa', $event->getName());
        $this->assertEquals(\App\Enum\DrawStatus::DRAFT, $event->getStatus());
    }
}
