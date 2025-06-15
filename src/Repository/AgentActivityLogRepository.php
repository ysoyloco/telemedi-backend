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
    public function getAgentAnalytics(Agent $agent, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('q.queueName, COUNT(a.id) as totalActivities, SUM(CASE WHEN a.wasSuccessful = true THEN 1 ELSE 0 END) as successfulActivities')
            ->join('a.queue', 'q')
            ->where('a.agent = :agent')
            ->groupBy('q.id')
            ->setParameter('agent', $agent);
        
        if ($startDate) {
            $qb->andWhere('a.activityStartDatetime >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        
        if ($endDate) {
            $qb->andWhere('a.activityEndDatetime <= :endDate')
               ->setParameter('endDate', $endDate);
        }
        
        $results = $qb->getQuery()->getArrayResult();
        
        // Oblicz średni czas obsługi
        $avgTimeQb = $this->createQueryBuilder('a')
            ->select('q.queueName, AVG(TIMESTAMPDIFF(SECOND, a.activityStartDatetime, a.activityEndDatetime)) as avgDuration')
            ->join('a.queue', 'q')
            ->where('a.agent = :agent')
            ->groupBy('q.id')
            ->setParameter('agent', $agent);
        
        if ($startDate) {
            $avgTimeQb->andWhere('a.activityStartDatetime >= :startDate')
                     ->setParameter('startDate', $startDate);
        }
        
        if ($endDate) {
            $avgTimeQb->andWhere('a.activityEndDatetime <= :endDate')
                     ->setParameter('endDate', $endDate);
        }
        
        $avgTimeResults = $avgTimeQb->getQuery()->getArrayResult();
        
        // Połącz wyniki
        $queuesData = [];
        foreach ($results as $result) {
            $queueName = $result['queueName'];
            $successRate = $result['totalActivities'] > 0 
                ? round(($result['successfulActivities'] / $result['totalActivities']) * 100, 2)
                : 0;
            
            $queuesData[$queueName] = [
                'totalActivities' => $result['totalActivities'],
                'successfulActivities' => $result['successfulActivities'],
                'successRate' => $successRate,
                'avgDuration' => 0
            ];
        }
        
        foreach ($avgTimeResults as $avgTime) {
            $queueName = $avgTime['queueName'];
            if (isset($queuesData[$queueName])) {
                $queuesData[$queueName]['avgDuration'] = round($avgTime['avgDuration']);
            }
        }
        
        // Oblicz ogólne statystyki
        $totalActivities = 0;
        $totalSuccessful = 0;
        $weightedAvgDuration = 0;
        $totalWeight = 0;
        
        foreach ($queuesData as $data) {
            $totalActivities += $data['totalActivities'];
            $totalSuccessful += $data['successfulActivities'];
            $weightedAvgDuration += $data['avgDuration'] * $data['totalActivities'];
            $totalWeight += $data['totalActivities'];
        }
        
        $overallSuccessRate = $totalActivities > 0 ? round(($totalSuccessful / $totalActivities) * 100, 2) : 0;
        $overallAvgDuration = $totalWeight > 0 ? round($weightedAvgDuration / $totalWeight) : 0;
        
        return [
            'agent' => [
                'id' => $agent->getId(),
                'fullName' => $agent->getFullName(),
            ],
            'period' => [
                'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
                'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            ],
            'overall' => [
                'totalActivities' => $totalActivities,
                'successfulActivities' => $totalSuccessful,
                'successRate' => $overallSuccessRate,
                'avgDuration' => $overallAvgDuration,
            ],
            'queues' => $queuesData,
        ];
    }
} 
 