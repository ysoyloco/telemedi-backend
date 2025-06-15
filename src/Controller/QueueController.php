<?php

namespace App\Controller;

use App\Service\QueueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class QueueController extends AbstractController
{
    public function __construct(
        private QueueService $queueService
    ) {
    }

    /**
     * Pobierz listę wszystkich kolejek posortowanych według priorytetu
     */
    #[Route('/queues', name: 'api_queues_list', methods: ['GET'])]
    public function getQueues(Request $request): JsonResponse
    {
        $queues = $this->queueService->getAllQueuesSortedByPriority();

        $response = [];
        foreach ($queues as $queue) {
            $response[] = [
                'id' => $queue->getId(),
                'queue_name' => $queue->getQueueName(),
                'priority' => $queue->getPriority(),
                'target_handled_calls_per_slot' => $queue->getTargetHandledCallsPerSlot(),
                'target_success_rate_percentage' => $queue->getTargetSuccessRatePercentage()
            ];
        }

        return $this->json($response);
    }
} 
 