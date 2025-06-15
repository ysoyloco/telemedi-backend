<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Repository\AgentActivityLogRepository;
use App\Repository\AgentRepository;
use App\Repository\ScheduleRepository;
use App\Service\AgentService;
use App\Service\SlotProposalService;
use PHPUnit\Framework\TestCase;

class SlotProposalServiceTest extends TestCase
{
    private AgentRepository $agentRepository;
    private AgentService $agentService;
    private AgentActivityLogRepository $activityLogRepository;
    private ScheduleRepository $scheduleRepository;
    private SlotProposalService $slotProposalService;

    protected function setUp(): void
    {
        $this->agentRepository = $this->createMock(AgentRepository::class);
        $this->agentService = $this->createMock(AgentService::class);
        $this->activityLogRepository = $this->createMock(AgentActivityLogRepository::class);
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        
        $this->slotProposalService = new SlotProposalService(
            $this->agentRepository,
            $this->agentService,
            $this->activityLogRepository,
            $this->scheduleRepository
        );
    }

    public function testGenerateProposalsForSlot(): void
    {
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        $queue->setId(1);
        
        $agent1 = new Agent();
        $agent1->setId(1);
        $agent1->setFullName('Jan Kowalski');
        $agent1->setIsActive(true);
        
        $agent2 = new Agent();
        $agent2->setId(2);
        $agent2->setFullName('Anna Nowak');
        $agent2->setIsActive(true);
        
        $slotStartDatetime = new \DateTime('2025-01-15 10:00:00');
        $slotEndDatetime = new \DateTime('2025-01-15 11:00:00');
        
        // Przygotuj odpowiedzi mock'ów
        $this->agentRepository->expects($this->once())
            ->method('findActiveAgentsForQueue')
            ->with($queue)
            ->willReturn([$agent1, $agent2]);
        
        // Agent 1 dostępny, Agent 2 niedostępny
        $this->agentService->expects($this->exactly(2))
            ->method('isAgentAvailableAt')
            ->willReturnMap([
                [$agent1, $slotStartDatetime, true],
                [$agent2, $slotStartDatetime, false],
            ]);
        
        $this->agentService->expects($this->once())
            ->method('getAgentUnavailabilityReason')
            ->with($agent2, $slotStartDatetime)
            ->willReturn('Już pracuje w tym czasie');
        
        // Symuluj metryki wydajności
        $this->activityLogRepository->expects($this->exactly(2))
            ->method('getSimplePerformanceMetric')
            ->willReturnMap([
                [$agent1, $queue, 'Świetny'],
                [$agent2, $queue, 'Dobry'],
            ]);
        
        // Wywołaj metodę
        $proposals = $this->slotProposalService->generateProposalsForSlot(
            $queue, $slotStartDatetime, $slotEndDatetime
        );
        
        // Sprawdź wyniki
        $this->assertIsArray($proposals);
        $this->assertArrayHasKey('slot_info', $proposals);
        $this->assertArrayHasKey('suggested_agents', $proposals);
        
        $this->assertEquals(1, $proposals['slot_info']['queue_id']);
        $this->assertEquals('Test Queue', $proposals['slot_info']['queue_name']);
        $this->assertEquals($slotStartDatetime->format('Y-m-d\TH:i:s\Z'), $proposals['slot_info']['slot_start_datetime']);
        $this->assertEquals($slotEndDatetime->format('Y-m-d\TH:i:s\Z'), $proposals['slot_info']['slot_end_datetime']);
        
        $this->assertCount(2, $proposals['suggested_agents']);
        
        // Sprawdź czy dostępny agent jest pierwszy
        $this->assertEquals(1, $proposals['suggested_agents'][0]['agent_id']);
        $this->assertEquals('Jan Kowalski', $proposals['suggested_agents'][0]['full_name']);
        $this->assertTrue($proposals['suggested_agents'][0]['is_available']);
        $this->assertNull($proposals['suggested_agents'][0]['availability_conflict_reason']);
        $this->assertEquals('Świetny', $proposals['suggested_agents'][0]['simple_performance_metric']);
        
        // Sprawdź czy niedostępny agent jest drugi
        $this->assertEquals(2, $proposals['suggested_agents'][1]['agent_id']);
        $this->assertEquals('Anna Nowak', $proposals['suggested_agents'][1]['full_name']);
        $this->assertFalse($proposals['suggested_agents'][1]['is_available']);
        $this->assertEquals('Już pracuje w tym czasie', $proposals['suggested_agents'][1]['availability_conflict_reason']);
        $this->assertEquals('Dobry', $proposals['suggested_agents'][1]['simple_performance_metric']);
    }
} 