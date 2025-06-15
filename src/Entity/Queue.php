<?php

namespace App\Entity;

use App\Repository\QueueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QueueRepository::class)]
#[ORM\Table(name: 'queues')]
class Queue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['queue:read', 'schedule:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['queue:read', 'schedule:read'])]
    private ?string $queueName = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['queue:read'])]
    private ?int $priority = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['queue:read'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['queue:read'])]
    private ?int $targetHandledCallsPerSlot = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Groups(['queue:read'])]
    private ?string $targetSuccessRatePercentage = null;

    #[ORM\ManyToMany(targetEntity: Agent::class, mappedBy: 'queues')]
    private Collection $agents;

    #[ORM\OneToMany(mappedBy: 'queue', targetEntity: Schedule::class)]
    private Collection $schedules;

    #[ORM\OneToMany(mappedBy: 'queue', targetEntity: AgentActivityLog::class)]
    private Collection $agentActivityLogs;

    #[ORM\OneToMany(mappedBy: 'queue', targetEntity: QueueLoadTrend::class)]
    private Collection $queueLoadTrends;

    public function __construct()
    {
        $this->agents = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->agentActivityLogs = new ArrayCollection();
        $this->queueLoadTrends = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQueueName(): ?string
    {
        return $this->queueName;
    }

    public function setQueueName(string $queueName): static
    {
        $this->queueName = $queueName;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTargetHandledCallsPerSlot(): ?int
    {
        return $this->targetHandledCallsPerSlot;
    }

    public function setTargetHandledCallsPerSlot(?int $targetHandledCallsPerSlot): static
    {
        $this->targetHandledCallsPerSlot = $targetHandledCallsPerSlot;

        return $this;
    }

    public function getTargetSuccessRatePercentage(): ?string
    {
        return $this->targetSuccessRatePercentage;
    }

    public function setTargetSuccessRatePercentage(?string $targetSuccessRatePercentage): static
    {
        $this->targetSuccessRatePercentage = $targetSuccessRatePercentage;

        return $this;
    }

    /**
     * @return Collection<int, Agent>
     */
    public function getAgents(): Collection
    {
        return $this->agents;
    }

    public function addAgent(Agent $agent): static
    {
        if (!$this->agents->contains($agent)) {
            $this->agents->add($agent);
            $agent->addQueue($this);
        }

        return $this;
    }

    public function removeAgent(Agent $agent): static
    {
        if ($this->agents->removeElement($agent)) {
            $agent->removeQueue($this);
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
            $schedule->setQueue($this);
        }

        return $this;
    }

    public function removeSchedule(Schedule $schedule): static
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getQueue() === $this) {
                $schedule->setQueue(null);
            }
        }

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
            $agentActivityLog->setQueue($this);
        }

        return $this;
    }

    public function removeAgentActivityLog(AgentActivityLog $agentActivityLog): static
    {
        if ($this->agentActivityLogs->removeElement($agentActivityLog)) {
            // set the owning side to null (unless already changed)
            if ($agentActivityLog->getQueue() === $this) {
                $agentActivityLog->setQueue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QueueLoadTrend>
     */
    public function getQueueLoadTrends(): Collection
    {
        return $this->queueLoadTrends;
    }

    public function addQueueLoadTrend(QueueLoadTrend $queueLoadTrend): static
    {
        if (!$this->queueLoadTrends->contains($queueLoadTrend)) {
            $this->queueLoadTrends->add($queueLoadTrend);
            $queueLoadTrend->setQueue($this);
        }

        return $this;
    }

    public function removeQueueLoadTrend(QueueLoadTrend $queueLoadTrend): static
    {
        if ($this->queueLoadTrends->removeElement($queueLoadTrend)) {
            // set the owning side to null (unless already changed)
            if ($queueLoadTrend->getQueue() === $this) {
                $queueLoadTrend->setQueue(null);
            }
        }

        return $this;
    }
} 
 