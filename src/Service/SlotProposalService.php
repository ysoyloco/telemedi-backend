<?php

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Repository\AgentActivityLogRepository;
use App\Repository\AgentRepository;
use App\Repository\ScheduleRepository;

class SlotProposalService
{
    public function __construct(
        private AgentRepository $agentRepository,
        private AgentService $agentService,
        private AgentActivityLogRepository $agentActivityLogRepository,
        private ScheduleRepository $scheduleRepository
    ) {
    }

    /**
     * Generuj propozycje agentów dla danego slotu
     *
     * @return array Lista propozycji agentów z informacjami o dostępności i wydajności
     */
    public function generateProposalsForSlot(
        Queue $queue,
        \DateTimeInterface $slotStartDatetime,
        \DateTimeInterface $slotEndDatetime
    ): array {
        // Pobierz wszystkich aktywnych agentów, którzy mogą obsługiwać daną kolejkę
        $agents = $this->agentRepository->findActiveAgentsForQueue($queue);
        
        $proposals = [];
        
        foreach ($agents as $agent) {
            // Sprawdź dostępność agenta
            $isAvailable = $this->agentService->isAgentAvailableAt($agent, $slotStartDatetime);
            
            // Pobierz powód niedostępności, jeśli agent jest niedostępny
            $unavailabilityReason = null;
            if (!$isAvailable) {
                $unavailabilityReason = $this->agentService->getAgentUnavailabilityReason($agent, $slotStartDatetime);
            }
            
            // Pobierz prostą metrykę wydajności
            $performanceMetric = $this->agentActivityLogRepository->getSimplePerformanceMetric($agent, $queue);
            
            // Dodaj propozycję do listy
            $proposals[] = [
                'agent_id' => $agent->getId(),
                'full_name' => $agent->getFullName(),
                'is_available' => $isAvailable,
                'availability_conflict_reason' => $unavailabilityReason,
                'simple_performance_metric' => $performanceMetric
            ];
        }
        
        // Sortuj propozycje - najpierw dostępni, potem według wydajności
        usort($proposals, function ($a, $b) {
            // Najpierw dostępni
            if ($a['is_available'] && !$b['is_available']) {
                return -1;
            }
            if (!$a['is_available'] && $b['is_available']) {
                return 1;
            }
            
            // Następnie według wydajności (prosta logika - lepsze metryki na górze)
            $performanceOrder = [
                'Świetny' => 1,
                'Dobry' => 2,
                'OK' => 3,
                'Wymaga poprawy' => 4
            ];
            
            $aPerformance = $performanceOrder[$a['simple_performance_metric']] ?? 5;
            $bPerformance = $performanceOrder[$b['simple_performance_metric']] ?? 5;
            
            return $aPerformance <=> $bPerformance;
        });
        
        return [
            'slot_info' => [
                'queue_id' => $queue->getId(),
                'queue_name' => $queue->getQueueName(),
                'slot_start_datetime' => $slotStartDatetime->format('Y-m-d\TH:i:s\Z'),
                'slot_end_datetime' => $slotEndDatetime->format('Y-m-d\TH:i:s\Z')
            ],
            'suggested_agents' => $proposals
        ];
    }
} 
 