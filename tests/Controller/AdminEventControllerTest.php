<?php

namespace App\Tests\Controller;

use App\Tests\Fixtures\EventFixtures;

class AdminEventControllerTest extends BaseControllerTest
{
    public function testAdminDashboardRequiresAuthentication(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);

        $this->client->request('GET', "/event/{$event->getId()}/admin-dashboard");

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessDashboard(): void
    {
        $event = EventFixtures::createActiveEvent($this->em);
        $participants = EventFixtures::createMultipleParticipants($this->em, $event, 3);

        $this->authenticateAsAdmin($event->getId(), $event->getAdminAccessToken());

        $this->client->request('GET', "/event/{$event->getId()}/admin-dashboard");

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Test Secret Santa');

        foreach ($participants as $participant) {
            $this->assertSelectorTextContains('body', $participant->getName());
        }
    }

    public function testAdminCannotAccessOtherEventDashboard(): void
    {
        $event1 = EventFixtures::createActiveEvent($this->em, 'admin1@test.com');
        $event2 = EventFixtures::createActiveEvent($this->em, 'admin2@test.com');

        $this->authenticateAsAdmin($event1->getId(), $event1->getAdminAccessToken());

        $this->client->request('GET', "/event/{$event2->getId()}/admin-dashboard");

        $this->assertResponseStatusCodeSame(403);
    }
}
