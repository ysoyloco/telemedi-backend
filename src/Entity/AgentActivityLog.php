<?php

namespace App\Entity;

use App\Repository\AgentActivityLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AgentActivityLogRepository::class)]
#[ORM\Table(name: 'agent_activity_log')]
class AgentActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['agent_activity_log:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'agentActivityLogs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['agent_activity_log:read'])]
    private ?Agent $agent = null;

    #[ORM\ManyToOne(inversedBy: 'agentActivityLogs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['agent_activity_log:read'])]
    private ?Queue $queue = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['agent_activity_log:read'])]
    private ?\DateTimeInterface $activityStartDatetime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['agent_activity_log:read'])]
    private ?\DateTimeInterface $activityEndDatetime = null;

    #[ORM\Column]
    #[Groups(['agent_activity_log:read'])]
    private ?bool $wasSuccessful = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['agent_activity_log:read'])]
    private ?string $activityReferenceId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    public function setAgent(?Agent $agent): static
    {
        $this->agent = $agent;

        return $this;
    }

    public function getQueue(): ?Queue
    {
        return $this->queue;
    }

    public function setQueue(?Queue $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function getActivityStartDatetime(): ?\DateTimeInterface
    {
        return $this->activityStartDatetime;
    }

    public function setActivityStartDatetime(\DateTimeInterface $activityStartDatetime): static
    {
        $this->activityStartDatetime = $activityStartDatetime;

        return $this;
    }

    public function getActivityEndDatetime(): ?\DateTimeInterface
    {
        return $this->activityEndDatetime;
    }

    public function setActivityEndDatetime(\DateTimeInterface $activityEndDatetime): static
    {
        $this->activityEndDatetime = $activityEndDatetime;

        return $this;
    }

    public function isWasSuccessful(): ?bool
    {
        return $this->wasSuccessful;
    }

    public function setWasSuccessful(bool $wasSuccessful): static
    {
        $this->wasSuccessful = $wasSuccessful;

        return $this;
    }

    public function getActivityReferenceId(): ?string
    {
        return $this->activityReferenceId;
    }

    public function setActivityReferenceId(?string $activityReferenceId): static
    {
        $this->activityReferenceId = $activityReferenceId;

        return $this;
    }

    /**
     * Calculate duration of activity in seconds
     */
    public function getDurationInSeconds(): int
    {
        if (!$this->activityStartDatetime || !$this->activityEndDatetime) {
            return 0;
        }

        return $this->activityEndDatetime->getTimestamp() - $this->activityStartDatetime->getTimestamp();
    }
} 
 