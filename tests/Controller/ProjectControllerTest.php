<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\Project;
use App\Enum\Priority;
use App\Enum\ProjectStatus;

final class ProjectControllerTest extends AuthenticatedWebTestCase
{
    public function testListsActiveProjectsWithBadges(): void
    {
        $customer = (new Client())->setFirstName('Ada')->setLastName('Lovelace')->setCompany('Analytical Engines');
        $active = (new Project())
            ->setName('Refonte du site vitrine')
            ->setPriority(Priority::Critical)
            ->setStatus(ProjectStatus::InProgress)
            ->setDueDate(new \DateTimeImmutable('-3 days'))
            ->setClient($customer);
        $deleted = (new Project())->setName('Projet supprimé')->setClient($customer)->softDelete();

        $this->em->persist($customer);
        $this->em->persist($active);
        $this->em->persist($deleted);
        $this->em->flush();

        $crawler = $this->browser->request('GET', '/project');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Projets');
        self::assertSelectorExists('a[aria-current="page"][href="/project"]');

        $table = $crawler->filter('table')->text();
        self::assertStringContainsString('Refonte du site vitrine', $table);
        self::assertStringContainsString('Ada Lovelace', $table);
        self::assertStringContainsString('Critique', $table);
        self::assertStringContainsString('En cours', $table);
        self::assertStringContainsString('(en retard)', $table);
        self::assertStringNotContainsString('Projet supprimé', $table);
    }

    public function testPaginatesProjectsTenPerPage(): void
    {
        $customer = (new Client())->setFirstName('Grace')->setLastName('Hopper');
        $this->em->persist($customer);
        for ($i = 1; $i <= 12; ++$i) {
            $this->em->persist((new Project())
                ->setName(sprintf('Projet %02d', $i))
                ->setDueDate(new \DateTimeImmutable(sprintf('+%d days', $i)))
                ->setClient($customer));
        }
        $this->em->persist((new Project())->setName('Supprimé')->setClient($customer)->softDelete());
        $this->em->flush();

        $crawler = $this->browser->request('GET', '/project');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1 + p', '12 projets actifs'); // the soft-deleted one is not counted
        self::assertCount(10, $crawler->filter('tbody tr'));
        self::assertSelectorTextContains('nav[aria-label="Pagination"]', 'Affichage de 1 à 10 sur 12');

        $crawler = $this->browser->click($crawler->filter('a[rel="next"]')->link());

        self::assertResponseIsSuccessful();
        self::assertCount(2, $crawler->filter('tbody tr'));
        self::assertSelectorTextContains('tbody tr:first-child', 'Projet 11');
    }

    public function testMostUrgentFirstAndProjectsWithoutDueDateLast(): void
    {
        $customer = (new Client())->setFirstName('Grace')->setLastName('Hopper');
        $this->em->persist($customer);
        $this->em->persist((new Project())->setName('Sans échéance')->setClient($customer));
        $this->em->persist((new Project())->setName('Dans un mois')->setDueDate(new \DateTimeImmutable('+1 month'))->setClient($customer));
        $this->em->persist((new Project())->setName('En retard')->setDueDate(new \DateTimeImmutable('-2 days'))->setClient($customer));
        $this->em->flush();

        $crawler = $this->browser->request('GET', '/project');

        $names = $crawler->filter('tbody tr td:first-child p:first-child')->each(static fn ($node): string => $node->text());
        self::assertSame(['En retard', 'Dans un mois', 'Sans échéance'], $names);
    }

    public function testOutOfRangePageReturns404(): void
    {
        $this->browser->request('GET', '/project?page=99');

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowsEmptyStateWithoutProjects(): void
    {
        // Soft-delete everything visible inside the transaction (rolled back afterwards).
        $this->em->createQuery('UPDATE App\Entity\Project p SET p.deletedAt = CURRENT_TIMESTAMP()')->execute();

        $this->browser->request('GET', '/project');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('table');
        self::assertSelectorTextContains('main', "Aucun projet pour l'instant.");
    }
}
