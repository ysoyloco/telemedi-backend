<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Repository\AgentActivityLogRepository;
use App\Repository\AgentAvailabilityExceptionRepository;
use App\Repository\AgentRepository;
use App\Repository\ScheduleRepository;
use App\Service\AgentService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgentServiceTest extends TestCase
{
    private AgentRepository $agentRepository;
    private AgentAvailabilityExceptionRepository $availabilityExceptionRepository;
    private ScheduleRepository $scheduleRepository;
    private AgentActivityLogRepository $activityLogRepository;
    private AgentService $agentService;

    protected function setUp(): void
    {
        $this->agentRepository = $this->createMock(AgentRepository::class);
        $this->availabilityExceptionRepository = $this->createMock(AgentAvailabilityExceptionRepository::class);
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->activityLogRepository = $this->createMock(AgentActivityLogRepository::class);
        
        $this->agentService = new AgentService(
            $this->agentRepository,
            $this->availabilityExceptionRepository,
            $this->scheduleRepository,
            $this->activityLogRepository
        );
    }

    public function testGetAgentById(): void
    {
        $agent = new Agent();
        $agent->setFullName('Jan Kowalski');
        
        $this->agentRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($agent);
        
        $result = $this->agentService->getAgentById(123);
        
        $this->assertSame($agent, $result);
        $this->assertEquals('Jan Kowalski', $result->getFullName());
    }
    
    public function testGetAgentByIdThrowsExceptionWhenNotFound(): void
    {
        $this->agentRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        $this->expectException(NotFoundHttpException::class);
        $this->agentService->getAgentById(999);
    }
    
    public function testGetActiveAgentsForQueue(): void
    {
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        
        $agent1 = new Agent();
        $agent1->setFullName('Jan Kowalski');
        
        $agent2 = new Agent();
        $agent2->setFullName('Anna Nowak');
        
        $this->agentRepository->expects($this->once())
            ->method('findActiveAgentsForQueue')
            ->with($queue)
            ->willReturn([$agent1, $agent2]);
        
        $results = $this->agentService->getActiveAgentsForQueue($queue);
        
        $this->assertCount(2, $results);
        $this->assertEquals('Jan Kowalski', $results[0]->getFullName());
        $this->assertEquals('Anna Nowak', $results[1]->getFullName());
    }
    
    public function testIsAgentAvailableAt(): void
    {
        $agent = new Agent();
        $agent->setIsActive(true);
        $agent->setDefaultAvailabilityPattern([
            'Mon' => ['08:00-16:00'],
            'Tue' => ['08:00-16:00'],
            'Wed' => ['08:00-16:00'],
            'Thu' => ['08:00-16:00'],
            'Fri' => ['08:00-16:00']
        ]);
        
        // Ustaw czas na poniedziałek, 10:00
        $dateTime = new \DateTime('2025-01-06 10:00:00'); // Monday
        
        // Brak wyjątków dostępności
        $this->availabilityExceptionRepository->expects($this->once())
            ->method('hasAvailabilityConflict')
            ->with($agent, $dateTime)
            ->willReturn(false);
        
        // Brak konfliktów w grafiku
        $this->scheduleRepository->expects($this->once())
            ->method('hasAgentScheduleConflict')
            ->willReturn(false);
        
        $available = $this->agentService->isAgentAvailableAt($agent, $dateTime);
        
        $this->assertTrue($available);
    }
    
    public function testGetAgentUnavailabilityReason(): void
    {
        $agent = new Agent();
        $agent->setIsActive(false); // Agent nieaktywny
        
        $dateTime = new \DateTime();
        
        $reason = $this->agentService->getAgentUnavailabilityReason($agent, $dateTime);
        
        $this->assertEquals('Agent nieaktywny', $reason);
    }
} 