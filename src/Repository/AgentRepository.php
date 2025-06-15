<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Queue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agent>
 *
 * @method Agent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agent[]    findAll()
 * @method Agent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agent::class);
    }

    /**
     * Znajdź dostępnych agentów dla danego okresu czasu
     *
     * @param \DateTime $startDate Data rozpoczęcia
     * @param \DateTime $endDate Data zakończenia
     * @param Queue|null $queue Opcjonalna kolejka do filtrowania
     * @return Agent[]
     */
    public function findAvailableAgentsForPeriod(\DateTime $startDate, \DateTime $endDate, ?Queue $queue = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.isActive = :isActive')
            ->setParameter('isActive', true);
        
        // Jeśli podano kolejkę, filtruj po umiejętnościach agenta
        if ($queue) {
            $qb->innerJoin('a.queues', 'q')
               ->andWhere('q.id = :queueId')
               ->setParameter('queueId', $queue->getId());
        }
        
        // Wyklucz agentów, którzy mają wyjątki dostępności w danym okresie
        $qb->andWhere('NOT EXISTS (
                SELECT e 
                FROM App\Entity\AgentAvailabilityException e 
                WHERE e.agent = a.id 
                AND e.unavailableDatetimeStart <= :endDate 
                AND e.unavailableDatetimeEnd >= :startDate
            )')
           ->setParameter('startDate', $startDate)
           ->setParameter('endDate', $endDate);
        
        // Wyklucz agentów, którzy już mają zaplanowane wpisy w grafiku w tym czasie
        $qb->andWhere('NOT EXISTS (
                SELECT s 
                FROM App\Entity\Schedule s 
                WHERE s.agent = a.id 
                AND s.scheduleDate = :scheduleDate
                AND s.timeSlotStart < :timeSlotEnd
                AND s.timeSlotEnd > :timeSlotStart
                AND s.entryStatus != :cancelledStatus
            )')
           ->setParameter('scheduleDate', $startDate->format('Y-m-d'))
           ->setParameter('timeSlotStart', $startDate->format('H:i:s'))
           ->setParameter('timeSlotEnd', $endDate->format('H:i:s'))
           ->setParameter('cancelledStatus', 'cancelled');
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Znajdź aktywnych agentów, którzy mają umiejętność obsługi danej kolejki
     *
     * @return Agent[]
     */
    public function findActiveAgentsForQueue(Queue $queue): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.queues', 'q')
            ->where('q.id = :queueId')
            ->andWhere('a.isActive = :isActive')
            ->setParameter('queueId', $queue->getId())
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź agenta po ID wraz z jego umiejętnościami (obsługiwanymi kolejkami)
     */
    public function findWithQueues(int $id): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.queues', 'q')
            ->addSelect('q')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Znajdź agenta po ID wraz z jego wyjątkami dostępności
     */
    public function findWithAvailabilityExceptions(int $id): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.agentAvailabilityExceptions', 'e')
            ->addSelect('e')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Znajdź agenta po ID wraz z jego grafikiem
     */
    public function findWithSchedules(int $id): ?Agent
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.schedules', 's')
            ->addSelect('s')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 
 