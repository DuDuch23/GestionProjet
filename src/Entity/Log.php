<?php

namespace App\Entity;

use App\Enum\LogAction;
use App\Repository\LogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * One history event on a project: who did what, and when.
 */
#[ORM\Entity(repositoryClass: LogRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_log_created_at', fields: ['createdAt'])]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    /** Null when the user was deleted, or for system actions. */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?User $performedBy = null;

    #[ORM\Column(length: 30, enumType: LogAction::class)]
    private ?LogAction $action = null;

    /**
     * Free-form context, e.g. changed fields: ['status' => ['todo', 'done']].
     *
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $details = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getPerformedBy(): ?User
    {
        return $this->performedBy;
    }

    public function setPerformedBy(?User $performedBy): static
    {
        $this->performedBy = $performedBy;

        return $this;
    }

    public function getAction(): ?LogAction
    {
        return $this->action;
    }

    public function setAction(LogAction $action): static
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * @param array<string, mixed>|null $details
     */
    public function setDetails(?array $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
