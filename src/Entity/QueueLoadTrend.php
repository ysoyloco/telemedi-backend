<?php

namespace App\Entity;

use App\Repository\QueueLoadTrendRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QueueLoadTrendRepository::class)]
#[ORM\Table(name: 'queue_load_trends')]
class QueueLoadTrend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['queue_load_trend:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'queueLoadTrends')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['queue_load_trend:read'])]
    private ?Queue $queue = null;

    #[ORM\Column]
    #[Groups(['queue_load_trend:read'])]
    private ?int $year = null;

    #[ORM\Column]
    #[Groups(['queue_load_trend:read'])]
    private ?int $quarter = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['queue_load_trend:read'])]
    private ?\DateTimeInterface $calculationDate = null;

    #[ORM\Column(length: 255)]
    #[Groups(['queue_load_trend:read'])]
    private ?string $metricName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['queue_load_trend:read'])]
    private ?string $metricValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['queue_load_trend:read'])]
    private ?string $additionalDescription = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getQuarter(): ?int
    {
        return $this->quarter;
    }

    public function setQuarter(int $quarter): static
    {
        $this->quarter = $quarter;

        return $this;
    }

    public function getCalculationDate(): ?\DateTimeInterface
    {
        return $this->calculationDate;
    }

    public function setCalculationDate(\DateTimeInterface $calculationDate): static
    {
        $this->calculationDate = $calculationDate;

        return $this;
    }

    public function getMetricName(): ?string
    {
        return $this->metricName;
    }

    public function setMetricName(string $metricName): static
    {
        $this->metricName = $metricName;

        return $this;
    }

    public function getMetricValue(): ?string
    {
        return $this->metricValue;
    }

    public function setMetricValue(string $metricValue): static
    {
        $this->metricValue = $metricValue;

        return $this;
    }

    public function getAdditionalDescription(): ?string
    {
        return $this->additionalDescription;
    }

    public function setAdditionalDescription(?string $additionalDescription): static
    {
        $this->additionalDescription = $additionalDescription;

        return $this;
    }
} 
 