<?php

namespace App\Repository;

use App\Entity\Queue;
use App\Entity\QueueLoadTrend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QueueLoadTrend>
 *
 * @method QueueLoadTrend|null find($id, $lockMode = null, $lockVersion = null)
 * @method QueueLoadTrend|null findOneBy(array $criteria, array $orderBy = null)
 * @method QueueLoadTrend[]    findAll()
 * @method QueueLoadTrend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueueLoadTrendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueueLoadTrend::class);
    }

    /**
     * Znajdź trendy obciążenia na podstawie filtrów
     *
     * @param Queue|null $queue
     * @param int|null $year
     * @param int|null $quarter
     * @param string|null $metricName
     * @return QueueLoadTrend[]
     */
    public function findByFilters(?Queue $queue = null, ?int $year = null, ?int $quarter = null, ?string $metricName = null): array
    {
        $qb = $this->createQueryBuilder('t');
        
        if ($queue) {
            $qb->andWhere('t.queue = :queue')
               ->setParameter('queue', $queue);
        }
        
        if ($year) {
            $qb->andWhere('t.year = :year')
               ->setParameter('year', $year);
        }
        
        if ($quarter) {
            $qb->andWhere('t.quarter = :quarter')
               ->setParameter('quarter', $quarter);
        }
        
        if ($metricName) {
            $qb->andWhere('t.metricName = :metricName')
               ->setParameter('metricName', $metricName);
        }
        
        return $qb->orderBy('t.year', 'DESC')
                 ->addOrderBy('t.quarter', 'DESC')
                 ->getQuery()
                 ->getResult();
    }
    
    /**
     * Pobierz trendy metryki dla kolejki w czasie
     *
     * @param Queue $queue
     * @return array
     */
    public function getQueueMetricsOverTime(Queue $queue): array
    {
        // Pobierz unikalne nazwy metryk dla kolejki
        $metricNames = $this->createQueryBuilder('t')
            ->select('DISTINCT t.metricName')
            ->where('t.queue = :queue')
            ->setParameter('queue', $queue)
            ->getQuery()
            ->getSingleColumnResult();
        
        $metrics = [];
        
        // Dla każdej metryki pobierz trendy w czasie
        foreach ($metricNames as $metricName) {
            $trends = $this->createQueryBuilder('t')
                ->where('t.queue = :queue')
                ->andWhere('t.metricName = :metricName')
                ->setParameter('queue', $queue)
                ->setParameter('metricName', $metricName)
                ->orderBy('t.year', 'ASC')
                ->addOrderBy('t.quarter', 'ASC')
                ->getQuery()
                ->getResult();
            
            $trendPoints = [];
            foreach ($trends as $trend) {
                $period = $trend->getYear() . '-Q' . $trend->getQuarter();
                $trendPoints[$period] = $trend->getMetricValue();
            }
            
            // Znajdź opis metryki z ostatniego trendu (jeśli istnieje)
            $description = count($trends) > 0 ? $trends[count($trends) - 1]->getAdditionalDescription() : '';
            
            $metrics[$metricName] = [
                'description' => $description,
                'values' => $trendPoints
            ];
        }
        
        return [
            'queue' => [
                'id' => $queue->getId(),
                'name' => $queue->getQueueName(),
            ],
            'metrics' => $metrics
        ];
    }

    /**
     * Znajdź trendy obciążenia dla danej kolejki i roku
     *
     * @return QueueLoadTrend[]
     */
    public function findByQueueAndYear(Queue $queue, int $year): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.queue = :queue')
            ->andWhere('t.year = :year')
            ->setParameter('queue', $queue)
            ->setParameter('year', $year)
            ->orderBy('t.quarter', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź trendy obciążenia dla danej kolejki, roku i kwartału
     *
     * @return QueueLoadTrend[]
     */
    public function findByQueueYearAndQuarter(Queue $queue, int $year, int $quarter): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.queue = :queue')
            ->andWhere('t.year = :year')
            ->andWhere('t.quarter = :quarter')
            ->setParameter('queue', $queue)
            ->setParameter('year', $year)
            ->setParameter('quarter', $quarter)
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź najnowszy trend obciążenia dla danej kolejki i metryki
     */
    public function findLatestByQueueAndMetric(Queue $queue, string $metricName): ?QueueLoadTrend
    {
        return $this->createQueryBuilder('t')
            ->where('t.queue = :queue')
            ->andWhere('t.metricName = :metricName')
            ->setParameter('queue', $queue)
            ->setParameter('metricName', $metricName)
            ->orderBy('t.year', 'DESC')
            ->addOrderBy('t.quarter', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
} 
 