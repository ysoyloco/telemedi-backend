<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\AgentAvailabilityException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgentAvailabilityException>
 *
 * @method AgentAvailabilityException|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgentAvailabilityException|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgentAvailabilityException[]    findAll()
 * @method AgentAvailabilityException[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentAvailabilityExceptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgentAvailabilityException::class);
    }

    /**
     * Znajdź wyjątki dostępności na podstawie filtrów
     *
     * @param Agent|null $agent
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return AgentAvailabilityException[]
     */
    public function findByFilters(?Agent $agent = null, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $qb = $this->createQueryBuilder('e');
        
        if ($agent) {
            $qb->andWhere('e.agent = :agent')
               ->setParameter('agent', $agent);
        }
        
        if ($startDate && $endDate) {
            $qb->andWhere(
                '(e.unavailableDatetimeStart <= :endDate AND e.unavailableDatetimeEnd >= :startDate)'
            )
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
        } elseif ($startDate) {
            $qb->andWhere('e.unavailableDatetimeEnd >= :startDate')
               ->setParameter('startDate', $startDate);
        } elseif ($endDate) {
            $qb->andWhere('e.unavailableDatetimeStart <= :endDate')
               ->setParameter('endDate', $endDate);
        }
        
        return $qb->orderBy('e.unavailableDatetimeStart', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    /**
     * Sprawdza, czy agent jest dostępny w danym czasie
     * (odwrotność metody hasAvailabilityConflict)
     * 
     * @param Agent $agent
     * @param \DateTimeInterface $dateTime
     * @return bool true jeśli agent jest dostępny (nie ma konfliktu)
     */
    public function isAgentAvailable(Agent $agent, \DateTimeInterface $dateTime): bool
    {
        return !$this->hasAvailabilityConflict($agent, $dateTime);
    }

    /**
     * Znajdź wyjątki dostępności agenta, które kolidują z danym zakresem dat i godzin
     *
     * @return AgentAvailabilityException[]
     */
    public function findConflictingExceptions(Agent $agent, \DateTimeInterface $startDatetime, \DateTimeInterface $endDatetime): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.agent = :agent')
            ->andWhere(
                '(e.unavailableDatetimeStart <= :endDatetime AND e.unavailableDatetimeEnd >= :startDatetime)'
            )
            ->setParameter('agent', $agent)
            ->setParameter('startDatetime', $startDatetime)
            ->setParameter('endDatetime', $endDatetime)
            ->getQuery()
            ->getResult();
    }

    /**
     * Sprawdź, czy agent ma wyjątek dostępności w danym czasie
     */
    public function hasAvailabilityConflict(Agent $agent, \DateTimeInterface $dateTime): bool
    {
        return count($this->createQueryBuilder('e')
            ->where('e.agent = :agent')
            ->andWhere('e.unavailableDatetimeStart <= :dateTime')
            ->andWhere('e.unavailableDatetimeEnd >= :dateTime')
            ->setParameter('agent', $agent)
            ->setParameter('dateTime', $dateTime)
            ->getQuery()
            ->getResult()) > 0;
    }

    /**
     * Pobierz wszystkie wyjątki dostępności agenta dla danego okresu
     *
     * @return AgentAvailabilityException[]
     */
    public function findByAgentAndDateRange(Agent $agent, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.agent = :agent')
            ->andWhere(
                '(e.unavailableDatetimeStart <= :endDate AND e.unavailableDatetimeEnd >= :startDate)'
            )
            ->setParameter('agent', $agent)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
} 
 