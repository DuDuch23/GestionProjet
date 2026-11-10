<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\UserFixtures;
use App\Entity\Attachment;
use App\Entity\Client;
use App\Entity\Log;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\Priority;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Loads the real fixtures (same loader as `doctrine:fixtures:load`) inside a
 * rolled-back transaction, so a schema change that breaks them fails here.
 */
final class FixturesTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();

        $loader = self::getContainer()->get('doctrine.fixtures.loader');
        (new ORMExecutor($this->em, new ORMPurger($this->em)))->execute($loader->getFixtures());
        $this->em->clear();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testExpectedVolumes(): void
    {
        self::assertSame(4, $this->em->getRepository(User::class)->count([]));
        self::assertSame(24, $this->em->getRepository(Client::class)->count([]));
        self::assertSame(30, $this->em->getRepository(Project::class)->count([]));
        self::assertSame(20, $this->em->getRepository(Attachment::class)->count([]));
        self::assertGreaterThan(30, $this->em->getRepository(Log::class)->count([]));

        /** @var ProjectRepository $projects */
        $projects = $this->em->getRepository(Project::class);
        self::assertCount(28, $projects->createActiveQueryBuilder()->getQuery()->getResult(), 'two projects are soft-deleted');
    }

    public function testEveryStatusAndPriorityIsRepresented(): void
    {
        $projects = $this->em->getRepository(Project::class)->findAll();

        $statuses = array_values(array_unique(array_map(static fn (Project $p): string => $p->getStatus()->value, $projects)));
        $priorities = array_values(array_unique(array_map(static fn (Project $p): string => $p->getPriority()->value, $projects)));

        self::assertEqualsCanonicalizing(array_column(ProjectStatus::cases(), 'value'), $statuses);
        self::assertEqualsCanonicalizing(array_column(Priority::cases(), 'value'), $priorities);
        self::assertNotEmpty(array_filter($projects, static fn (Project $p): bool => $p->isOverdue()), 'some projects are overdue');
        self::assertNotEmpty(array_filter($projects, static fn (Project $p): bool => $p->getImages()->count() > 0), 'some projects have images');
    }

    public function testAdminCanLogInWithTheDocumentedPassword(): void
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@gestionprojet.test']);

        self::assertNotNull($admin);
        self::assertContains('ROLE_ADMIN', $admin->getRoles());
        self::assertTrue(self::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($admin, UserFixtures::PASSWORD));
    }

    public function testClientEmailsAreUniqueAndFictional(): void
    {
        $emails = array_filter(array_map(
            static fn (Client $c): ?string => $c->getEmail(),
            $this->em->getRepository(Client::class)->findAll(),
        ));

        self::assertSame($emails, array_unique($emails));
        foreach ($emails as $email) {
            self::assertStringEndsWith('.example', $email, 'only reserved domains, so no real person gets an email');
        }
    }
}
