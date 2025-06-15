<?php

namespace App\Controller;

use App\Service\AgentService;
use App\Service\QueueService;
use App\Service\ScheduleService;
use App\Service\SlotProposalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private ScheduleService $scheduleService,
        private QueueService $queueService,
        private AgentService $agentService,
        private SlotProposalService $slotProposalService,
        private SerializerInterface $serializer
    ) {
    }

    /**
     * Pobierz wszystkie wpisy w grafiku
     */
    #[Route('/schedules', name: 'api_schedules_index', methods: ['GET'])]
    public function getSchedules(Request $request): JsonResponse
    {
        $agentId = $request->query->get('agent_id');
        $queueId = $request->query->get('queue_id');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        $schedules = $this->scheduleService->getSchedules(
            $agentId ? (int) $agentId : null,
            $queueId ? (int) $queueId : null,
            $startDate ? new \DateTime($startDate) : null,
            $endDate ? new \DateTime($endDate) : null
        );

        return $this->json($schedules, Response::HTTP_OK, [], ['groups' => 'schedule:read']);
    }

    /**
     * Pobierz dane dla widoku kalendarza
     */
    #[Route('/calendar-view', name: 'api_calendar_view', methods: ['GET'])]
    public function getCalendarView(Request $request): JsonResponse
    {
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');
        $queueId = $request->query->get('queue_id');

        if (!$startDate || !$endDate || !$queueId) {
            throw new BadRequestHttpException('Wymagane parametry: start_date, end_date, queue_id');
        }

        $startDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);
        $queue = $this->queueService->getQueueById((int) $queueId);

        $scheduleEntries = $this->scheduleService->getScheduleEntries($queue, $startDate, $endDate);

        return $this->json([
            'queue_info' => [
                'id' => $queue->getId(),
                'queue_name' => $queue->getQueueName(),
                'priority' => $queue->getPriority()
            ],
            'schedule_entries' => $scheduleEntries
        ], Response::HTTP_OK, [], ['groups' => 'schedule:read']);
    }

    /**
     * Pobierz propozycje agentów dla wybranego slotu
     */
    #[Route('/slot-proposals', name: 'api_slot_proposals', methods: ['GET'])]
    public function getSlotProposals(Request $request): JsonResponse
    {
        $queueId = $request->query->get('queue_id');
        $slotStartDateTime = $request->query->get('slot_start_datetime');
        $slotEndDateTime = $request->query->get('slot_end_datetime');

        if (!$queueId || !$slotStartDateTime || !$slotEndDateTime) {
            throw new BadRequestHttpException('Wymagane parametry: queue_id, slot_start_datetime, slot_end_datetime');
        }

        $queue = $this->queueService->getQueueById((int) $queueId);
        $startDateTime = new \DateTime($slotStartDateTime);
        $endDateTime = new \DateTime($slotEndDateTime);

        $proposals = $this->slotProposalService->generateProposalsForSlot($queue, $startDateTime, $endDateTime);

        return $this->json($proposals);
    }

    /**
     * Utwórz nowy wpis w grafiku
     */
    #[Route('/schedules', name: 'api_schedules_create', methods: ['POST'])]
    public function createScheduleEntry(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['agent_id'], $data['queue_id'], $data['schedule_date'], $data['time_slot_start'], $data['time_slot_end'], $data['entry_status'])) {
            throw new BadRequestHttpException('Brakuje wymaganych pól');
        }

        $agent = $this->agentService->getAgentById((int) $data['agent_id']);
        $queue = $this->queueService->getQueueById((int) $data['queue_id']);
        $scheduleDate = new \DateTime($data['schedule_date']);
        $timeSlotStart = new \DateTime($data['time_slot_start']);
        $timeSlotEnd = new \DateTime($data['time_slot_end']);

        $schedule = $this->scheduleService->createScheduleEntry(
            $agent,
            $queue,
            $scheduleDate,
            $timeSlotStart,
            $timeSlotEnd,
            $data['entry_status']
        );

        return $this->json($schedule, Response::HTTP_CREATED, [], ['groups' => 'schedule:read']);
    }

    /**
     * Aktualizuj istniejący wpis w grafiku
     */
    #[Route('/schedules/{schedule_entry_id}', name: 'api_schedules_update', methods: ['PUT'])]
    public function updateScheduleEntry(Request $request, int $schedule_entry_id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Przygotuj opcjonalne parametry
        $agent = isset($data['agent_id']) ? $this->agentService->getAgentById((int) $data['agent_id']) : null;
        $queue = isset($data['queue_id']) ? $this->queueService->getQueueById((int) $data['queue_id']) : null;
        $scheduleDate = isset($data['schedule_date']) ? new \DateTime($data['schedule_date']) : null;
        $timeSlotStart = isset($data['time_slot_start']) ? new \DateTime($data['time_slot_start']) : null;
        $timeSlotEnd = isset($data['time_slot_end']) ? new \DateTime($data['time_slot_end']) : null;
        $entryStatus = $data['entry_status'] ?? null;

        $schedule = $this->scheduleService->updateScheduleEntry(
            $schedule_entry_id,
            $agent,
            $queue,
            $scheduleDate,
            $timeSlotStart,
            $timeSlotEnd,
            $entryStatus
        );

        return $this->json($schedule, Response::HTTP_OK, [], ['groups' => 'schedule:read']);
    }

    /**
     * Usuń wpis z grafiku
     */
    #[Route('/schedules/{schedule_entry_id}', name: 'api_schedules_delete', methods: ['DELETE'])]
    public function deleteScheduleEntry(int $schedule_entry_id): JsonResponse
    {
        $this->scheduleService->deleteScheduleEntry($schedule_entry_id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
} 
 