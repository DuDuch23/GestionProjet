<?php

namespace App\Tests\Entity;

use App\Entity\Attachment;
use App\Entity\Client;
use App\Entity\Log;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\LogAction;
use App\Enum\Priority;
use App\Enum\ProjectStatus;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ProjectPersistenceTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testProjectIsPersistedWithClientAttachmentsAndLogs(): void
    {
        $user = (new User())->setEmail('test@example.com')->setPassword('hash');
        $client = (new Client())->setFirstName('Ada')->setLastName('Lovelace')->setCompany('Analytical');

        $project = (new Project())
            ->setName('Website redesign')
            ->setDescription(str_repeat('Long description. ', 50))
            ->setPriority(Priority::High)
            ->setStatus(ProjectStatus::InProgress)
            ->setDueDate(new \DateTimeImmutable('2027-01-15'))
            ->setClient($client);

        $project->addAttachment($this->attachment('mockup.png', 'image/png', $user));
        $project->addAttachment($this->attachment('quote.pdf', 'application/pdf', $user));
        $project->addLog((new Log())->setAction(LogAction::Created)->setPerformedBy($user));

        $this->em->persist($user);
        $this->em->persist($client);
        $this->em->persist($project); // attachments & logs cascade
        $this->em->flush();
        $this->em->clear();

        $reloaded = $this->em->find(Project::class, $project->getId());

        self::assertSame('Website redesign', $reloaded->getName());
        self::assertSame(Priority::High, $reloaded->getPriority());
        self::assertSame(ProjectStatus::InProgress, $reloaded->getStatus());
        self::assertSame('2027-01-15', $reloaded->getDueDate()->format('Y-m-d'));
        self::assertSame('Lovelace', $reloaded->getClient()->getLastName());
        self::assertNotNull($reloaded->getCreatedAt());
        self::assertNotNull($reloaded->getUpdatedAt());

        self::assertCount(2, $reloaded->getAttachments());
        self::assertCount(1, $reloaded->getImages());
        self::assertSame('mockup.png', $reloaded->getImages()->first()->getOriginalName());
        self::assertCount(1, $reloaded->getDocuments());

        self::assertCount(1, $reloaded->getLogs());
        $log = $reloaded->getLogs()->first();
        self::assertSame(LogAction::Created, $log->getAction());
        self::assertSame('test@example.com', $log->getPerformedBy()->getEmail());
        self::assertNotNull($log->getCreatedAt());
    }

    public function testSoftDeletedProjectsAreExcludedFromActiveList(): void
    {
        $client = (new Client())->setFirstName('Grace')->setLastName('Hopper');
        $kept = (new Project())->setName('Kept')->setClient($client);
        $deleted = (new Project())->setName('Deleted')->setClient($client)->softDelete();

        $this->em->persist($client);
        $this->em->persist($kept);
        $this->em->persist($deleted);
        $this->em->flush();

        /** @var ProjectRepository $repository */
        $repository = $this->em->getRepository(Project::class);
        $names = array_map(static fn (Project $p): ?string => $p->getName(), $repository->createActiveQueryBuilder()->getQuery()->getResult());

        self::assertTrue($deleted->isDeleted());
        self::assertContains('Kept', $names);
        self::assertNotContains('Deleted', $names);
    }

    public function testRemovingAnAttachmentFromProjectDeletesIt(): void
    {
        $client = (new Client())->setFirstName('Alan')->setLastName('Turing');
        $attachment = $this->attachment('spec.pdf', 'application/pdf');
        $project = (new Project())->setName('Enigma')->setClient($client)->addAttachment($attachment);

        $this->em->persist($client);
        $this->em->persist($project);
        $this->em->flush();
        $attachmentId = $attachment->getId();

        $project->removeAttachment($attachment); // orphanRemoval
        $this->em->flush();
        $this->em->clear();

        self::assertNull($this->em->find(Attachment::class, $attachmentId));
    }

    private function attachment(string $name, string $mimeType, ?User $user = null): Attachment
    {
        return (new Attachment())
            ->setOriginalName($name)
            ->setFileName(bin2hex(random_bytes(16)).'.'.pathinfo($name, \PATHINFO_EXTENSION))
            ->setMimeType($mimeType)
            ->setSize(1234)
            ->setUploadedBy($user);
    }
}
