<?php

namespace App\Controller;

use App\Entity\QueueLoadTrend;
use App\Repository\QueueLoadTrendRepository;
use App\Repository\QueueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/queue-load-trends')]
class QueueLoadTrendController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private QueueLoadTrendRepository $trendRepository;
    private QueueRepository $queueRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        QueueLoadTrendRepository $trendRepository,
        QueueRepository $queueRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->trendRepository = $trendRepository;
        $this->queueRepository = $queueRepository;
    }

    #[Route('', name: 'queue_load_trend_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $queueId = $request->query->get('queue_id');
        $year = $request->query->get('year');
        $quarter = $request->query->get('quarter');
        $metricName = $request->query->get('metric_name');
        
        // Jeśli są podane parametry, użyj niestandardowej metody wyszukiwania
        if ($queueId || $year || $quarter || $metricName) {
            $trends = $this->trendRepository->findByFilters(
                $queueId ? $this->queueRepository->find($queueId) : null,
                $year ? (int)$year : null,
                $quarter ? (int)$quarter : null,
                $metricName
            );
        } else {
            // W przeciwnym razie pobierz wszystkie trendy
            $trends = $this->trendRepository->findAll();
        }
        
        return $this->json($trends, Response::HTTP_OK, [], ['groups' => ['queue_load_trend:read']]);
    }

    #[Route('/{id}', name: 'queue_load_trend_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $trend = $this->trendRepository->find($id);
        
        if (!$trend) {
            return $this->json(['message' => 'Queue load trend not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($trend, Response::HTTP_OK, [], ['groups' => ['queue_load_trend:read']]);
    }

    #[Route('', name: 'queue_load_trend_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['queue_id']) || !isset($data['year']) || !isset($data['quarter']) || !isset($data['metricName']) || !isset($data['metricValue'])) {
            return $this->json(['message' => 'Missing required parameters'], Response::HTTP_BAD_REQUEST);
        }
        
        $queue = $this->queueRepository->find($data['queue_id']);
        
        if (!$queue) {
            return $this->json(['message' => 'Queue not found'], Response::HTTP_NOT_FOUND);
        }
        
        $trend = new QueueLoadTrend();
        $trend->setQueue($queue);
        $trend->setYear($data['year']);
        $trend->setQuarter($data['quarter']);
        $trend->setMetricName($data['metricName']);
        $trend->setMetricValue($data['metricValue']);
        
        if (isset($data['calculationDate'])) {
            $trend->setCalculationDate(new \DateTime($data['calculationDate']));
        } else {
            $trend->setCalculationDate(new \DateTime());
        }
        
        if (isset($data['additionalDescription'])) {
            $trend->setAdditionalDescription($data['additionalDescription']);
        }
        
        $this->entityManager->persist($trend);
        $this->entityManager->flush();
        
        return $this->json($trend, Response::HTTP_CREATED, [], ['groups' => ['queue_load_trend:read']]);
    }

    #[Route('/{id}', name: 'queue_load_trend_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $trend = $this->trendRepository->find($id);
        
        if (!$trend) {
            return $this->json(['message' => 'Queue load trend not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['queue_id'])) {
            $queue = $this->queueRepository->find($data['queue_id']);
            if (!$queue) {
                return $this->json(['message' => 'Queue not found'], Response::HTTP_NOT_FOUND);
            }
            $trend->setQueue($queue);
        }
        
        if (isset($data['year'])) {
            $trend->setYear($data['year']);
        }
        
        if (isset($data['quarter'])) {
            $trend->setQuarter($data['quarter']);
        }
        
        if (isset($data['calculationDate'])) {
            $trend->setCalculationDate(new \DateTime($data['calculationDate']));
        }
        
        if (isset($data['metricName'])) {
            $trend->setMetricName($data['metricName']);
        }
        
        if (isset($data['metricValue'])) {
            $trend->setMetricValue($data['metricValue']);
        }
        
        if (isset($data['additionalDescription'])) {
            $trend->setAdditionalDescription($data['additionalDescription']);
        }
        
        $this->entityManager->flush();
        
        return $this->json($trend, Response::HTTP_OK, [], ['groups' => ['queue_load_trend:read']]);
    }

    #[Route('/{id}', name: 'queue_load_trend_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $trend = $this->trendRepository->find($id);
        
        if (!$trend) {
            return $this->json(['message' => 'Queue load trend not found'], Response::HTTP_NOT_FOUND);
        }
        
        $this->entityManager->remove($trend);
        $this->entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/analytics/queue/{id}', name: 'queue_metrics_analytics', methods: ['GET'])]
    public function getQueueMetricsAnalytics(int $id): JsonResponse
    {
        $queue = $this->queueRepository->find($id);
        
        if (!$queue) {
            return $this->json(['message' => 'Queue not found'], Response::HTTP_NOT_FOUND);
        }
        
        $metrics = $this->trendRepository->getQueueMetricsOverTime($queue);
        
        return $this->json($metrics, Response::HTTP_OK);
    }
} 