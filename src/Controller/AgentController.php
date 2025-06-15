<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Repository\AgentRepository;
use App\Repository\QueueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/agents')]
class AgentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private AgentRepository $agentRepository;
    private QueueRepository $queueRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        AgentRepository $agentRepository,
        QueueRepository $queueRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->agentRepository = $agentRepository;
        $this->queueRepository = $queueRepository;
    }

    #[Route('', name: 'agent_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $agents = $this->agentRepository->findAll();
        return $this->json($agents, Response::HTTP_OK, [], ['groups' => ['agent:read']]);
    }

    #[Route('/{id}', name: 'agent_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);

        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($agent, Response::HTTP_OK, [], ['groups' => ['agent:read']]);
    }

    #[Route('', name: 'agent_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $agent = new Agent();
        $agent->setFullName($data['fullName']);
        
        if (isset($data['email'])) {
            $agent->setEmail($data['email']);
        }
        
        if (isset($data['defaultAvailabilityPattern'])) {
            $agent->setDefaultAvailabilityPattern($data['defaultAvailabilityPattern']);
        }
        
        if (isset($data['isActive'])) {
            $agent->setIsActive($data['isActive']);
        }
        
        if (isset($data['queues']) && is_array($data['queues'])) {
            foreach ($data['queues'] as $queueId) {
                $queue = $this->queueRepository->find($queueId);
                if ($queue) {
                    $agent->addQueue($queue);
                }
            }
        }
        
        $this->entityManager->persist($agent);
        $this->entityManager->flush();
        
        return $this->json($agent, Response::HTTP_CREATED, [], ['groups' => ['agent:read']]);
    }

    #[Route('/{id}', name: 'agent_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);
        
        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['fullName'])) {
            $agent->setFullName($data['fullName']);
        }
        
        if (isset($data['email'])) {
            $agent->setEmail($data['email']);
        }
        
        if (isset($data['defaultAvailabilityPattern'])) {
            $agent->setDefaultAvailabilityPattern($data['defaultAvailabilityPattern']);
        }
        
        if (isset($data['isActive'])) {
            $agent->setIsActive($data['isActive']);
        }
        
        if (isset($data['queues']) && is_array($data['queues'])) {
            // Remove all existing queues
            foreach ($agent->getQueues() as $existingQueue) {
                $agent->removeQueue($existingQueue);
            }
            
            // Add new queues
            foreach ($data['queues'] as $queueId) {
                $queue = $this->queueRepository->find($queueId);
                if ($queue) {
                    $agent->addQueue($queue);
                }
            }
        }
        
        $this->entityManager->flush();
        
        return $this->json($agent, Response::HTTP_OK, [], ['groups' => ['agent:read']]);
    }

    #[Route('/{id}', name: 'agent_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);
        
        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }
        
        $this->entityManager->remove($agent);
        $this->entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/queues', name: 'agent_queues', methods: ['GET'])]
    public function getAgentQueues(int $id): JsonResponse
    {
        $agent = $this->agentRepository->find($id);
        
        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($agent->getQueues(), Response::HTTP_OK, [], ['groups' => ['queue:read']]);
    }

    #[Route('/available/for-period', name: 'agent_available_for_period', methods: ['GET'])]
    public function getAvailableAgentsForPeriod(Request $request): JsonResponse
    {
        $startDate = new \DateTime($request->query->get('start_date'));
        $endDate = new \DateTime($request->query->get('end_date'));
        $queueId = $request->query->get('queue_id');
        
        $queue = null;
        if ($queueId) {
            $queue = $this->queueRepository->find($queueId);
            if (!$queue) {
                return $this->json(['message' => 'Queue not found'], Response::HTTP_NOT_FOUND);
            }
        }
        
        $availableAgents = $this->agentRepository->findAvailableAgentsForPeriod($startDate, $endDate, $queue);
        
        return $this->json($availableAgents, Response::HTTP_OK, [], ['groups' => ['agent:read']]);
    }
} 