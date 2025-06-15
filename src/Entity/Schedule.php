<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
#[ORM\Table(name: 'schedules')]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['schedule:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read'])]
    private ?Agent $agent = null;

    #[ORM\ManyToOne(inversedBy: 'schedules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read'])]
    private ?Queue $queue = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['schedule:read'])]
    private ?\DateTimeInterface $scheduleDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['schedule:read'])]
    private ?\DateTimeInterface $timeSlotStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['schedule:read'])]
    private ?\DateTimeInterface $timeSlotEnd = null;

    #[ORM\Column(length: 50)]
    #[Groups(['schedule:read'])]
    private ?string $entryStatus = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['schedule:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['schedule:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

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

    public function getScheduleDate(): ?\DateTimeInterface
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(\DateTimeInterface $scheduleDate): static
    {
        $this->scheduleDate = $scheduleDate;

        return $this;
    }

    public function getTimeSlotStart(): ?\DateTimeInterface
    {
        return $this->timeSlotStart;
    }

    public function setTimeSlotStart(\DateTimeInterface $timeSlotStart): static
    {
        $this->timeSlotStart = $timeSlotStart;

        return $this;
    }

    public function getTimeSlotEnd(): ?\DateTimeInterface
    {
        return $this->timeSlotEnd;
    }

    public function setTimeSlotEnd(\DateTimeInterface $timeSlotEnd): static
    {
        $this->timeSlotEnd = $timeSlotEnd;

        return $this;
    }

    public function getEntryStatus(): ?string
    {
        return $this->entryStatus;
    }

    public function setEntryStatus(string $entryStatus): static
    {
        $this->entryStatus = $entryStatus;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Helper method to create a formatted response for calendar view
     */
    #[Groups(['schedule:read'])]
    public function getTitle(): string
    {
        return $this->agent ? 
            $this->agent->getFullName() . ' (' . $this->queue->getQueueName() . ')' : 
            'Niezdefiniowany agent';
    }

    /**
     * Helper method for React Big Calendar
     */
    #[Groups(['schedule:read'])]
    public function getStart(): \DateTimeInterface
    {
        $startDateTime = clone $this->scheduleDate;
        $startTimeHours = (int) $this->timeSlotStart->format('H');
        $startTimeMinutes = (int) $this->timeSlotStart->format('i');
        $startDateTime->setTime($startTimeHours, $startTimeMinutes);
        
        return $startDateTime;
    }

    /**
     * Helper method for React Big Calendar
     */
    #[Groups(['schedule:read'])]
    public function getEnd(): \DateTimeInterface
    {
        $endDateTime = clone $this->scheduleDate;
        $endTimeHours = (int) $this->timeSlotEnd->format('H');
        $endTimeMinutes = (int) $this->timeSlotEnd->format('i');
        $endDateTime->setTime($endTimeHours, $endTimeMinutes);
        
        return $endDateTime;
    }

    /**
     * Helper method for React Big Calendar
     */
    #[Groups(['schedule:read'])]
    public function getResourceId(): ?int
    {
        return $this->agent ? $this->agent->getId() : null;
    }
} 
 