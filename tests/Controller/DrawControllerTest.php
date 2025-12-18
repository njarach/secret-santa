<?php

namespace App\Tests\Controller;

use App\Tests\Fixtures\EventFixtures;
use Symfony\Component\Mailer\MailerInterface;

class DrawControllerTest extends BaseControllerTest
{
    public function testAdminCanPerformDraw(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock->expects($this->exactly(3))->method('send'); // 3 participants = 3 emails
        static::getContainer()->set(MailerInterface::class, $mailerMock);

        $event = EventFixtures::createActiveEvent($this->em);
        $participants = EventFixtures::createMultipleParticipants($this->em, $event, 3);

        $this->authenticateAsAdmin($event->getId(), $event->getAdminAccessToken());

        $this->client->request('POST', "/event/{$event->getId()}/perform-draw");

        $this->assertResponseRedirects();

        $this->em->refresh($event);

        $this->assertEquals(\App\Enum\DrawStatus::DRAWN, $event->getStatus());

        $draws = $event->getDraws();
        $this->assertCount(3, $draws);

        foreach ($participants as $participant) {
            $this->em->refresh($participant);
            $this->assertNotNull($participant->getDraw());
        }
    }

    public function testCannotPerformDrawWithLessThan3Participants(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        EventFixtures::createMultipleParticipants($this->em, $event, 2); // Seulement 2 participants

        $this->authenticateAsAdmin($event->getId(), $event->getAdminAccessToken());

        $this->client->request('POST', "/event/{$event->getId()}/perform-draw");

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertSelectorExists('.flash-error');
    }

    public function testAdminCanRevealDraws(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participants = EventFixtures::createMultipleParticipants($this->em, $event, 3);

        $drawService = static::getContainer()->get(\App\Services\DrawService::class);
        $drawService->performDraw($event);

        $this->authenticateAsAdmin($event->getId(), $event->getAdminAccessToken());

        $this->client->request('GET', "/event/{$event->getId()}/reveal-draws");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Révélation des tirages');

        foreach ($participants as $participant) {
            $this->assertSelectorTextContains('body', $participant->getName());
        }
    }
}
