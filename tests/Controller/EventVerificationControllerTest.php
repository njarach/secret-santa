<?php

namespace App\Tests\Controller;

use App\Tests\Fixtures\EventFixtures;
use Symfony\Component\Mailer\MailerInterface;

class EventVerificationControllerTest extends BaseControllerTest
{
    public function testVerifyEventWithValidToken(): void
    {
        $mailerMock = $this->createMock(MailerInterface::class);
        $mailerMock->expects($this->once())->method('send');
        static::getContainer()->set(MailerInterface::class, $mailerMock);

        $event = EventFixtures::createDraftEvent($this->em, 'verify@test.com');
        $token = $event->getVerificationToken();

        $this->client->request('GET', "/event/verify/{$event->getId()}/$token");

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->em->refresh($event);

        $this->assertEquals(\App\Enum\DrawStatus::ACTIVE, $event->getStatus());
        $this->assertNotNull($event->getVerifiedAt());
        $this->assertNull($event->getVerificationToken());
    }

    public function testVerifyEventWithInvalidToken(): void
    {
        $event = EventFixtures::createDraftEvent($this->em);

        $this->client->request('GET', "/event/verify/{$event->getId()}/invalid-token");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Vérification échouée');
    }
}
