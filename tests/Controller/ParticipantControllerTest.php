<?php

namespace App\Tests\Controller;

use App\Tests\Fixtures\EventFixtures;

class ParticipantControllerTest extends BaseControllerTest
{
    public function testParticipantCanAccessTheirDashboard(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participant = EventFixtures::createParticipant($this->em, $event, 'John Doe', 'john@test.com');

        $this->authenticateAsParticipant($event->getId(), $participant->getEventAccessToken());

        $this->client->request('GET', "/event/{$event->getId()}/participant-dashboard");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Bienvenue John Doe');
    }

    public function testParticipantCanUpdateTheirName(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participant = EventFixtures::createParticipant($this->em, $event, 'Old Name', 'test@test.com');

        $this->authenticateAsParticipant($event->getId(), $participant->getEventAccessToken());

        $this->client->request('POST', "/participant/{$participant->getId()}/update-name", [
            'name' => 'New Name'
        ]);

        $this->assertResponseIsSuccessful();

        $this->em->refresh($participant);

        $this->assertEquals('New Name', $participant->getName());
    }

    public function testParticipantCanUpdateTheirWishlist(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participant = EventFixtures::createParticipant($this->em, $event, 'Test User', 'test@test.com');

        $this->authenticateAsParticipant($event->getId(), $participant->getEventAccessToken());

        $this->client->request('POST', "/participant/{$participant->getId()}/update-wishlist", [
            'wishlist' => 'Un livre, des chaussettes'
        ]);

        $this->assertResponseIsSuccessful();

        $this->em->refresh($participant);

        $this->assertEquals('Un livre, des chaussettes', $participant->getWishlist());
    }

    public function testParticipantCannotUpdateOtherParticipantData(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participant1 = EventFixtures::createParticipant($this->em, $event, 'User 1', 'user1@test.com');
        $participant2 = EventFixtures::createParticipant($this->em, $event, 'User 2', 'user2@test.com');

        $this->authenticateAsParticipant($event->getId(), $participant1->getEventAccessToken());

        $this->client->request('POST', "/participant/{$participant2->getId()}/update-name", [
            'name' => 'Hacked Name'
        ]);

        $this->assertResponseIsSuccessful();

        $this->em->refresh($participant2);

        $this->assertEquals('User 2', $participant2->getName());
    }
}
