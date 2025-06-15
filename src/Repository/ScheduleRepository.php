<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Schedule>
 *
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    /**
     * Znajdź wpisy grafiku z opcjonalnym filtrowaniem
     *
     * @param int|null $agentId ID agenta (opcjonalne)
     * @param int|null $queueId ID kolejki (opcjonalne)
     * @param \DateTimeInterface|null $startDate Data początkowa (opcjonalna)
     * @param \DateTimeInterface|null $endDate Data końcowa (opcjonalna)
     * @return Schedule[]
     */
    public function findByFilters(
        ?int $agentId = null,
        ?int $queueId = null,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.agent', 'a')
            ->addSelect('a')
            ->innerJoin('s.queue', 'q')
            ->addSelect('q');

        if ($agentId !== null) {
            $qb->andWhere('s.agent = :agentId')
               ->setParameter('agentId', $agentId);
        }

        if ($queueId !== null) {
            $qb->andWhere('s.queue = :queueId')
               ->setParameter('queueId', $queueId);
        }

        if ($startDate !== null) {
            $qb->andWhere('s.scheduleDate >= :startDate')
               ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            $qb->andWhere('s.scheduleDate <= :endDate')
               ->setParameter('endDate', $endDate);
        }

        return $qb->orderBy('s.scheduleDate', 'ASC')
                 ->addOrderBy('s.timeSlotStart', 'ASC')
                 ->getQuery()
                 ->getResult();
    }

    /**
     * Znajdź wszystkie wpisy grafiku dla określonej kolejki i zakresu dat
     *
     * @return Schedule[]
     */
    public function findByQueueAndDateRange(Queue $queue, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.agent', 'a')
            ->addSelect('a')
            ->innerJoin('s.queue', 'q')
            ->addSelect('q')
            ->where('s.queue = :queue')
            ->andWhere('s.scheduleDate >= :startDate')
            ->andWhere('s.scheduleDate <= :endDate')
            ->setParameter('queue', $queue)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.timeSlotStart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sprawdź, czy agent ma już zaplanowany wpis w grafiku w danym czasie
     */
    public function hasAgentScheduleConflict(Agent $agent, \DateTimeInterface $scheduleDate, \DateTimeInterface $timeSlotStart, \DateTimeInterface $timeSlotEnd, ?int $excludeScheduleId = null): bool
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.agent = :agent')
            ->andWhere('s.scheduleDate = :scheduleDate')
            ->andWhere(
                '(s.timeSlotStart < :timeSlotEnd AND s.timeSlotEnd > :timeSlotStart)'
            )
            ->setParameter('agent', $agent)
            ->setParameter('scheduleDate', $scheduleDate)
            ->setParameter('timeSlotStart', $timeSlotStart)
            ->setParameter('timeSlotEnd', $timeSlotEnd);

        if ($excludeScheduleId !== null) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeScheduleId);
        }

        return count($qb->getQuery()->getResult()) > 0;
    }

    /**
     * Pobierz wszystkie wpisy grafiku agenta w danym zakresie dat
     *
     * @return Schedule[]
     */
    public function findAgentSchedulesByDateRange(Agent $agent, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.queue', 'q')
            ->addSelect('q')
            ->where('s.agent = :agent')
            ->andWhere('s.scheduleDate >= :startDate')
            ->andWhere('s.scheduleDate <= :endDate')
            ->setParameter('agent', $agent)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.timeSlotStart', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 
 