<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanDatabase();
        $this->em->close();
    }

    protected function cleanDatabase(): void
    {
        $connection = $this->em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        $connection->executeStatement('TRUNCATE TABLE draw');
        $connection->executeStatement('TRUNCATE TABLE participant');
        $connection->executeStatement('TRUNCATE TABLE event');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function authenticateAsAdmin(int $eventId, string $token): void
    {
        $this->client->request('GET', '/home');

        $session = $this->client->getRequest()->getSession();
        $session->set('event_id', $eventId);
        $session->set('user_role', 'admin');
        $session->set('user_token', $token);
        $session->save();
    }

    protected function authenticateAsParticipant(int $eventId, string $token): void
    {
        $this->client->request('GET', '/home');

        $session = $this->client->getRequest()->getSession();
        $session->set('event_id', $eventId);
        $session->set('user_role', 'participant');
        $session->set('user_token', $token);
        $session->save();
    }
}
