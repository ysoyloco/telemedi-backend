<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\AgentActivityLog;
use App\Entity\Queue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgentActivityLog>
 *
 * @method AgentActivityLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgentActivityLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgentActivityLog[]    findAll()
 * @method AgentActivityLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgentActivityLog::class);
    }

    /**
     * Znajdź logi aktywności na podstawie filtrów
     *
     * @param Agent|null $agent
     * @param Queue|null $queue
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return AgentActivityLog[]
     */
    public function findByFilters(?Agent $agent = null, ?Queue $queue = null, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $qb = $this->createQueryBuilder('a');
        
        if ($agent) {
            $qb->andWhere('a.agent = :agent')
               ->setParameter('agent', $agent);
        }
        
        if ($queue) {
            $qb->andWhere('a.queue = :queue')
               ->setParameter('queue', $queue);
        }
        
        if ($startDate) {
            $qb->andWhere('a.activityStartDatetime >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        
        if ($endDate) {
            $qb->andWhere('a.activityEndDatetime <= :endDate')
               ->setParameter('endDate', $endDate);
        }
        
        return $qb->orderBy('a.activityStartDatetime', 'DESC')
                 ->getQuery()
                 ->getResult();
    }

    /**
     * Pobierz ostatnie aktywności agenta dla danej kolejki
     *
     * @return AgentActivityLog[]
     */
    public function findRecentActivitiesForAgentAndQueue(Agent $agent, Queue $queue, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.agent = :agent')
            ->andWhere('a.queue = :queue')
            ->setParameter('agent', $agent)
            ->setParameter('queue', $queue)
            ->orderBy('a.activityStartDatetime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Oblicz prostą metrykę wydajności agenta dla danej kolejki
     * Zwraca procent udanych połączeń
     */
    public function calculateAgentPerformanceForQueue(Agent $agent, Queue $queue): float
    {
        $result = $this->createQueryBuilder('a')
            ->select('COUNT(a.id) as total, SUM(CASE WHEN a.wasSuccessful = true THEN 1 ELSE 0 END) as successful')
            ->where('a.agent = :agent')
            ->andWhere('a.queue = :queue')
            ->setParameter('agent', $agent)
            ->setParameter('queue', $queue)
            ->getQuery()
            ->getSingleResult();

        if ($result['total'] === 0) {
            return 0;
        }

        return ($result['successful'] / $result['total']) * 100;
    }

    /**
     * Pobierz prostą ocenę wydajności (tekst) na podstawie procentu udanych połączeń
     */
    public function getSimplePerformanceMetric(Agent $agent, Queue $queue): string
    {
        $performancePercent = $this->calculateAgentPerformanceForQueue($agent, $queue);

        if ($performancePercent >= 90) {
            return 'Świetny';
        } elseif ($performancePercent >= 75) {
            return 'Dobry';
        } elseif ($performancePercent >= 60) {
            return 'OK';
        } else {
            return 'Wymaga poprawy';
        }
    }

    /**
     * Pobierz analitykę aktywności agenta
     *
     * @param Agent $agent
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return array
     */
    public function getAgentAnalytics(Agent $agent, ?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null): array
    {
        // PSEUDOKOD CO CHCEMY:
        // 1. Pobierz wszystkie activity logi dla agenta w danym okresie
        // 2. Pogrupuj po kolejkach 
        // 3. Dla każdej kolejki policz:
        //    - liczbę wszystkich aktywności
        //    - liczbę udanych aktywności  
        //    - średni czas trwania aktywności w sekundach
        //    - procent sukcesu
        
        $qb = $this->createQueryBuilder('a')
            ->select('a, q')
            ->join('a.queue', 'q')
            ->where('a.agent = :agent')
            ->setParameter('agent', $agent);
        
        if ($startDate) {
            $qb->andWhere('a.activityStartDatetime >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        
        if ($endDate) {
            $qb->andWhere('a.activityStartDatetime <= :endDate')
               ->setParameter('endDate', $endDate);
        }
        
        $logs = $qb->getQuery()->getResult();
        
        // Grupowanie i kalkulacje w PHP
        $analytics = [];
        $queueGroups = [];
        
        // Grupuj po kolejkach
        foreach ($logs as $log) {
            $queueName = $log->getQueue()->getQueueName();
            if (!isset($queueGroups[$queueName])) {
                $queueGroups[$queueName] = [];
            }
            $queueGroups[$queueName][] = $log;
        }
        
        // Policz statystyki dla każdej kolejki
        foreach ($queueGroups as $queueName => $queueLogs) {
            $totalActivities = count($queueLogs);
            $successfulActivities = 0;
            $totalDurationSeconds = 0;
            
            foreach ($queueLogs as $log) {
                if ($log->isWasSuccessful()) {
                    $successfulActivities++;
                }
                
                // Policz czas trwania w sekundach
                if ($log->getActivityStartDatetime() && $log->getActivityEndDatetime()) {
                    $duration = $log->getActivityEndDatetime()->getTimestamp() - 
                               $log->getActivityStartDatetime()->getTimestamp();
                    $totalDurationSeconds += $duration;
                }
            }
            
            $avgDurationSeconds = $totalActivities > 0 ? $totalDurationSeconds / $totalActivities : 0;
            $successRate = $totalActivities > 0 ? ($successfulActivities / $totalActivities) * 100 : 0;
            
            $analytics[] = [
                'queue_name' => $queueName,
                'total_activities' => $totalActivities,
                'successful_activities' => $successfulActivities,
                'success_rate_percentage' => round($successRate, 2),
                'average_duration_seconds' => round($avgDurationSeconds, 2)
            ];
        }
        
        return $analytics;
    }
} 
 