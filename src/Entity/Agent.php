<?php

namespace App\Entity;

use App\Repository\AgentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AgentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Agent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['agent:read', 'schedule:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['agent:read', 'schedule:read'])]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    #[Groups(['agent:read', 'schedule:read'])]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['agent:read'])]
    private array $defaultAvailabilityPattern = [];

    #[ORM\Column]
    #[Groups(['agent:read'])]
    private bool $isActive = true;

    #[ORM\ManyToMany(targetEntity: Queue::class, inversedBy: 'agents')]
    #[Groups(['agent:read'])]
    private Collection $queues;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: AgentActivityLog::class, orphanRemoval: true)]
    private Collection $agentActivityLogs;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: AgentAvailabilityException::class, orphanRemoval: true)]
    private Collection $agentAvailabilityExceptions;

    #[ORM\OneToMany(mappedBy: 'agent', targetEntity: Schedule::class, orphanRemoval: true)]
    private Collection $schedules;

    public function __construct()
    {
        $this->queues = new ArrayCollection();
        $this->agentActivityLogs = new ArrayCollection();
        $this->agentAvailabilityExceptions = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDefaultAvailabilityPattern(): array
    {
        return $this->defaultAvailabilityPattern;
    }

    public function setDefaultAvailabilityPattern(array $defaultAvailabilityPattern): static
    {
        $this->defaultAvailabilityPattern = $defaultAvailabilityPattern;

        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Queue>
     */
    public function getQueues(): Collection
    {
        return $this->queues;
    }

    public function addQueue(Queue $queue): static
    {
        if (!$this->queues->contains($queue)) {
            $this->queues->add($queue);
        }

        return $this;
    }

    public function removeQueue(Queue $queue): static
    {
        $this->queues->removeElement($queue);

        return $this;
    }

    /**
     * @return Collection<int, AgentActivityLog>
     */
    public function getAgentActivityLogs(): Collection
    {
        return $this->agentActivityLogs;
    }

    public function addAgentActivityLog(AgentActivityLog $agentActivityLog): static
    {
        if (!$this->agentActivityLogs->contains($agentActivityLog)) {
            $this->agentActivityLogs->add($agentActivityLog);
            $agentActivityLog->setAgent($this);
        }

        return $this;
    }

    public function removeAgentActivityLog(AgentActivityLog $agentActivityLog): static
    {
        if ($this->agentActivityLogs->removeElement($agentActivityLog)) {
            // set the owning side to null (unless already changed)
            if ($agentActivityLog->getAgent() === $this) {
                $agentActivityLog->setAgent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AgentAvailabilityException>
     */
    public function getAgentAvailabilityExceptions(): Collection
    {
        return $this->agentAvailabilityExceptions;
    }

    public function addAgentAvailabilityException(AgentAvailabilityException $agentAvailabilityException): static
    {
        if (!$this->agentAvailabilityExceptions->contains($agentAvailabilityException)) {
            $this->agentAvailabilityExceptions->add($agentAvailabilityException);
            $agentAvailabilityException->setAgent($this);
        }

        return $this;
    }

    public function removeAgentAvailabilityException(AgentAvailabilityException $agentAvailabilityException): static
    {
        if ($this->agentAvailabilityExceptions->removeElement($agentAvailabilityException)) {
            // set the owning side to null (unless already changed)
            if ($agentAvailabilityException->getAgent() === $this) {
                $agentAvailabilityException->setAgent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Schedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(Schedule $schedule): static
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setAgent($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): static
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getAgent() === $this) {
                $schedule->setAgent(null);
            }
        }

        return $this;
    }
} 