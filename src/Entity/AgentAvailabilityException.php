<?php

namespace App\Entity;

use App\Repository\AgentAvailabilityExceptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AgentAvailabilityExceptionRepository::class)]
#[ORM\Table(name: 'agent_availability_exceptions')]
class AgentAvailabilityException
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['agent_availability_exception:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'agentAvailabilityExceptions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['agent_availability_exception:read'])]
    private ?Agent $agent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['agent_availability_exception:read'])]
    private ?\DateTimeInterface $unavailableDatetimeStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['agent_availability_exception:read'])]
    private ?\DateTimeInterface $unavailableDatetimeEnd = null;

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

    public function getUnavailableDatetimeStart(): ?\DateTimeInterface
    {
        return $this->unavailableDatetimeStart;
    }

    public function setUnavailableDatetimeStart(?\DateTimeInterface $unavailableDatetimeStart): static
    {
        $this->unavailableDatetimeStart = $unavailableDatetimeStart;

        return $this;
    }

    public function getUnavailableDatetimeEnd(): ?\DateTimeInterface
    {
        return $this->unavailableDatetimeEnd;
    }

    public function setUnavailableDatetimeEnd(?\DateTimeInterface $unavailableDatetimeEnd): static
    {
        $this->unavailableDatetimeEnd = $unavailableDatetimeEnd;

        return $this;
    }

    /**
     * Check if the exception covers a specific datetime
     */
    public function isDatetimeUnavailable(\DateTimeInterface $datetime): bool
    {
        if ($this->unavailableDatetimeStart === null || $this->unavailableDatetimeEnd === null) {
            return false;
        }

        return $datetime >= $this->unavailableDatetimeStart && $datetime <= $this->unavailableDatetimeEnd;
    }
} 
 