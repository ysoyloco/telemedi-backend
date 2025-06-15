<?php

namespace App\Controller;

use App\Entity\AgentAvailabilityException;
use App\Repository\AgentAvailabilityExceptionRepository;
use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/agent-availability-exceptions')]
class AgentAvailabilityExceptionController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private AgentAvailabilityExceptionRepository $exceptionRepository;
    private AgentRepository $agentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        AgentAvailabilityExceptionRepository $exceptionRepository,
        AgentRepository $agentRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->exceptionRepository = $exceptionRepository;
        $this->agentRepository = $agentRepository;
    }

    #[Route('', name: 'agent_availability_exception_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $agentId = $request->query->get('agent_id');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        
        // Jeśli są podane parametry, użyj niestandardowej metody wyszukiwania
        if ($agentId || $startDate || $endDate) {
            $exceptions = $this->exceptionRepository->findByFilters(
                $agentId ? $this->agentRepository->find($agentId) : null,
                $startDate ? new \DateTime($startDate) : null,
                $endDate ? new \DateTime($endDate) : null
            );
        } else {
            // W przeciwnym razie pobierz wszystkie wyjątki
            $exceptions = $this->exceptionRepository->findAll();
        }
        
        return $this->json($exceptions, Response::HTTP_OK, [], ['groups' => ['agent_availability_exception:read']]);
    }

    #[Route('/{id}', name: 'agent_availability_exception_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $exception = $this->exceptionRepository->find($id);
        
        if (!$exception) {
            return $this->json(['message' => 'Availability exception not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json($exception, Response::HTTP_OK, [], ['groups' => ['agent_availability_exception:read']]);
    }

    #[Route('', name: 'agent_availability_exception_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['agent_id'])) {
            return $this->json(['message' => 'Missing required parameter: agent_id'], Response::HTTP_BAD_REQUEST);
        }
        
        $agent = $this->agentRepository->find($data['agent_id']);
        
        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }
        
        $exception = new AgentAvailabilityException();
        $exception->setAgent($agent);
        
        if (isset($data['unavailableDatetimeStart'])) {
            $exception->setUnavailableDatetimeStart(new \DateTime($data['unavailableDatetimeStart']));
        }
        
        if (isset($data['unavailableDatetimeEnd'])) {
            $exception->setUnavailableDatetimeEnd(new \DateTime($data['unavailableDatetimeEnd']));
        }
        
        $this->entityManager->persist($exception);
        $this->entityManager->flush();
        
        return $this->json($exception, Response::HTTP_CREATED, [], ['groups' => ['agent_availability_exception:read']]);
    }

    #[Route('/{id}', name: 'agent_availability_exception_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $exception = $this->exceptionRepository->find($id);
        
        if (!$exception) {
            return $this->json(['message' => 'Availability exception not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['agent_id'])) {
            $agent = $this->agentRepository->find($data['agent_id']);
            if (!$agent) {
                return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
            }
            $exception->setAgent($agent);
        }
        
        if (isset($data['unavailableDatetimeStart'])) {
            $exception->setUnavailableDatetimeStart(new \DateTime($data['unavailableDatetimeStart']));
        }
        
        if (isset($data['unavailableDatetimeEnd'])) {
            $exception->setUnavailableDatetimeEnd(new \DateTime($data['unavailableDatetimeEnd']));
        }
        
        $this->entityManager->flush();
        
        return $this->json($exception, Response::HTTP_OK, [], ['groups' => ['agent_availability_exception:read']]);
    }

    #[Route('/{id}', name: 'agent_availability_exception_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $exception = $this->exceptionRepository->find($id);
        
        if (!$exception) {
            return $this->json(['message' => 'Availability exception not found'], Response::HTTP_NOT_FOUND);
        }
        
        $this->entityManager->remove($exception);
        $this->entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/check/availability', name: 'check_agent_availability', methods: ['GET'])]
    public function checkAvailability(Request $request): JsonResponse
    {
        $agentId = $request->query->get('agent_id');
        $datetime = $request->query->get('datetime');
        
        if (!$agentId || !$datetime) {
            return $this->json(['message' => 'Missing required parameters: agent_id, datetime'], Response::HTTP_BAD_REQUEST);
        }
        
        $agent = $this->agentRepository->find($agentId);
        
        if (!$agent) {
            return $this->json(['message' => 'Agent not found'], Response::HTTP_NOT_FOUND);
        }
        
        $checkDatetime = new \DateTime($datetime);
        $isAvailable = $this->exceptionRepository->isAgentAvailable($agent, $checkDatetime);
        
        return $this->json(['agent_id' => $agent->getId(), 'datetime' => $datetime, 'is_available' => $isAvailable], Response::HTTP_OK);
    }
} 