<?php

namespace App\Tests\Controller;

use App\Tests\Fixtures\EventFixtures;

class EventAccessControllerTest extends BaseControllerTest
{
    public function testAccessWithAdminToken(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $token = $event->getAdminAccessToken();

        $this->client->request('GET', "/event/access/{$event->getId()}/$token");

        $this->assertResponseRedirects("/event/{$event->getId()}/admin-dashboard");
    }

    public function testAccessWithParticipantToken(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participant = EventFixtures::createParticipant($this->em, $event, 'Test User', 'test@test.com');
        $token = $participant->getEventAccessToken();

        $this->client->request('GET', "/event/access/{$event->getId()}/$token");

        $this->assertResponseRedirects("/event/{$event->getId()}/participant-dashboard");
    }

    public function testAccessWithInvalidToken(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);

        $this->expectException(\Symfony\Component\Finder\Exception\AccessDeniedException::class);

        $this->client->request('GET', "/event/access/{$event->getId()}/invalid-token");
    }
}
