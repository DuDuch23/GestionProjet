<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClientControllerTest extends WebTestCase
{
    private KernelBrowser $browser;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->browser = static::createClient();
        $this->browser->disableReboot(); // same DB connection for every request, so the transaction is visible
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testShowsEmptyStateWithoutClients(): void
    {
        $this->browser->request('GET', '/client');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', "Aucun client pour l'instant.");
        self::assertSelectorNotExists('nav[aria-label="Pagination"]');
    }

    public function testPaginatesClientsTenPerPage(): void
    {
        $this->createClients(12);

        $crawler = $this->browser->request('GET', '/client');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1 + p', '12 clients');
        self::assertCount(10, $crawler->filter('tbody tr'));
        self::assertSelectorTextContains('nav[aria-label="Pagination"]', 'Affichage de 1 à 10 sur 12');
        self::assertSelectorTextContains('nav[aria-label="Pagination"] [aria-current="page"]', '1');

        // Follow the "next page" link like a user would
        $crawler = $this->browser->click($crawler->filter('a[rel="next"]')->link());

        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('tbody tr'));
        self::assertSelectorTextContains('nav[aria-label="Pagination"]', 'Affichage de 11 à 12 sur 12');
        self::assertSelectorNotExists('a[rel="next"]');
    }

    public function testJoinedProjectsDoNotBreakPageSize(): void
    {
        // One client with many projects must still count as one row (LIMIT applies to clients, not to joined rows).
        $clients = $this->createClients(11);
        for ($i = 0; $i < 5; ++$i) {
            $this->em->persist((new Project())->setName('Projet '.$i)->setClient($clients[0]));
        }
        $this->em->flush();
        $this->em->clear(); // like a real request: reload clients from the DB, not from memory

        $crawler = $this->browser->request('GET', '/client');

        self::assertCount(10, $crawler->filter('tbody tr'));
        self::assertSelectorTextContains('tbody', 'Projet 4');
    }

    public function testOutOfRangePageReturns404(): void
    {
        $this->createClients(3);

        $this->browser->request('GET', '/client?page=5');

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @return list<Client>
     */
    private function createClients(int $count): array
    {
        $clients = [];
        for ($i = 1; $i <= $count; ++$i) {
            // "Client 01".."Client 12": sorted by last name, so clients[0] is on page 1
            $client = (new Client())->setFirstName('Test')->setLastName(sprintf('Client %02d', $i));
            $this->em->persist($client);
            $clients[] = $client;
        }
        $this->em->flush();

        return $clients;
    }
}
