<?php

namespace App\Controller;

use App\Entity\AgentActivityLog;
use App\Repository\AgentActivityLogRepository;
use App\Repository\AgentRepository;
use App\Repository\QueueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/agent-activity-logs')]
class AgentActivityLogController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private AgentActivityLogRepository $agentActivityLogRepository;
    private AgentRepository $agentRepository;
    private QueueRepository $queueRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        AgentActivityLogRepository $agentActivityLogRepository,
        AgentRepository $agentRepository,
        QueueRepository $queueRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->agentActivityLogRepository = $agentActivityLogRepository;
        $this->agentRepository = $agentRepository;
        $this->queueRepository = $queueRepository;
    }

    #[Route('', name: 'agent_activity_log_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $agentId = $request->query->get('agent_id');
        $queueId = $request->query->get('queue_id');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        
        // Jeśli są podane parametry, użyj niestandardowej metody wyszukiwania
        if ($agentId || $queueId || $startDate || $endDate) {
            $logs = $this->agentActivityLogRepository->findByFilters(
                $agentId ? $this->agentRepository->find($agentId) : null,
                $queueId ? $this->queueRepository->find($queueId) : null,
                $startDate ? new \DateTime($startDate) : null,
                $endDate ? new \DateTime($endDate) : null
            );
        } else {
            // W przeciwnym razie pobierz wszystkie logi
            $logs = $this->agentActivityLogRepository->findAll();
        }
        
        return $this->json($logs, Response::HTTP_OK, [], ['groups' => ['agent_activity_log:read']]);
    }

    #[Route('/{id}', name: 'agent_activity_log_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $log = $this->agentActivityLogRepository->find($id);
        
        if (!$log) {
            return $this->json(['message' => 'Activity log not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($log, Response::HTTP_OK, [], ['groups' => ['agent_activity_log:read']]);
    }

    #[Route('', name: 'agent_activity_log_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['agent_id']) || !isset($data['queue_id'])) {
            return $this->json(['message' => 'Missing required parameters: agent_id, queue_id'], Response::HTTP_BAD_REQUEST);
        }
        
        $agent = $this->agentRepository->find($data['agent_id']);
        $queue = $this->queueRepository->find($data['queue_id']);
        
        if (!$agent || !$queue) {
            return $this->json(['message' => 'Agent or Queue not found'], Response::HTTP_NOT_FOUND);
        }
        
        $log = new AgentActivityLog();
        $log->setAgent($agent);
        $log->setQueue($queue);
        
        if (isset($data['activityStartDatetime'])) {
            $log->setActivityStartDatetime(new \DateTime($data['activityStartDatetime']));
        } else {
            $log->setActivityStartDatetime(new \DateTime());
        }
        
        if (isset($data['activityEndDatetime'])) {
            $log->setActivityEndDatetime(new \DateTime($data['activityEndDatetime']));
        }
        
        if (isset($data['wasSuccessful'])) {
            $log->setWasSuccessful($data['wasSuccessful']);
        }
        
        if (isset($data['activityReferenceId'])) {
            $log->setActivityReferenceId($data['activityReferenceId']);
        }
        
        $this->entityManager->persist($log);
        $this->entityManager->flush();
        
        return $this->json($log, Response::HTTP_CREATED, [], ['groups' => ['agent_activity_log:read']]);
    }

    #[Route('/{id}', name: 'agent_activity_log_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $log = $this->agentActivityLogRepository->find($id);
        
        if (!$log) {
            return $this->json(['message' => 'Activity log not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['agent_id'])) {
            $agent = $this->agentRepository->find($data['agent_id']);
            if (!$agent) {
                return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
            }
            $log->setAgent($agent);
        }
        
        if (isset($data['queue_id'])) {
            $queue = $this->queueRepository->find($data['queue_id']);
            if (!$queue) {
                return $this->json(['message' => 'Queue not found'], Response::HTTP_NOT_FOUND);
            }
            $log->setQueue($queue);
        }
        
        if (isset($data['activityStartDatetime'])) {
            $log->setActivityStartDatetime(new \DateTime($data['activityStartDatetime']));
        }
        
        if (isset($data['activityEndDatetime'])) {
            $log->setActivityEndDatetime(new \DateTime($data['activityEndDatetime']));
        }
        
        if (isset($data['wasSuccessful'])) {
            $log->setWasSuccessful($data['wasSuccessful']);
        }
        
        if (isset($data['activityReferenceId'])) {
            $log->setActivityReferenceId($data['activityReferenceId']);
        }
        
        $this->entityManager->flush();
        
        return $this->json($log, Response::HTTP_OK, [], ['groups' => ['agent_activity_log:read']]);
    }

    #[Route('/{id}', name: 'agent_activity_log_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $log = $this->agentActivityLogRepository->find($id);
        
        if (!$log) {
            return $this->json(['message' => 'Activity log not found'], Response::HTTP_NOT_FOUND);
        }
        
        $this->entityManager->remove($log);
        $this->entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/analytics/agent/{id}', name: 'agent_activity_analytics', methods: ['GET'])]
    public function getAgentAnalytics(int $id, Request $request): JsonResponse
    {
        $agent = $this->agentRepository->find($id);
        
        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }
        
        $startDate = $request->query->get('start_date') ? new \DateTime($request->query->get('start_date')) : null;
        $endDate = $request->query->get('end_date') ? new \DateTime($request->query->get('end_date')) : null;
        
        $analytics = $this->agentActivityLogRepository->getAgentAnalytics($agent, $startDate, $endDate);
        
        return $this->json($analytics, Response::HTTP_OK);
    }
} 