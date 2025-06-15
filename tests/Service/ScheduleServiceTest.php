<?php

namespace App\Tests\Service;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Entity\Schedule;
use App\Repository\ScheduleRepository;
use App\Service\AgentService;
use App\Service\ScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScheduleServiceTest extends TestCase
{
    private ScheduleRepository $scheduleRepository;
    private AgentService $agentService;
    private EntityManagerInterface $entityManager;
    private ScheduleService $scheduleService;

    protected function setUp(): void
    {
        $this->scheduleRepository = $this->createMock(ScheduleRepository::class);
        $this->agentService = $this->createMock(AgentService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->scheduleService = new ScheduleService(
            $this->scheduleRepository,
            $this->agentService,
            $this->entityManager
        );
    }

    public function testGetScheduleEntries(): void
    {
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        
        $startDate = new \DateTime('2025-01-01');
        $endDate = new \DateTime('2025-01-31');
        
        $schedule = new Schedule();
        $schedule->setQueue($queue);
        
        $this->scheduleRepository->expects($this->once())
            ->method('findByQueueAndDateRange')
            ->with($queue, $startDate, $endDate)
            ->willReturn([$schedule]);
        
        $results = $this->scheduleService->getScheduleEntries($queue, $startDate, $endDate);
        
        $this->assertCount(1, $results);
        $this->assertSame($schedule, $results[0]);
    }
    
    public function testGetScheduleEntryById(): void
    {
        $schedule = new Schedule();
        $schedule->setEntryStatus('scheduled');
        
        $this->scheduleRepository->expects($this->once())
            ->method('find')
            ->with(123)
            ->willReturn($schedule);
        
        $result = $this->scheduleService->getScheduleEntryById(123);
        
        $this->assertSame($schedule, $result);
        $this->assertEquals('scheduled', $result->getEntryStatus());
    }
    
    public function testGetScheduleEntryByIdThrowsExceptionWhenNotFound(): void
    {
        $this->scheduleRepository->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);
        
        $this->expectException(NotFoundHttpException::class);
        $this->scheduleService->getScheduleEntryById(999);
    }
    
    public function testCreateScheduleEntry(): void
    {
        $agent = new Agent();
        $agent->setFullName('Jan Kowalski');
        $agent->setIsActive(true);
        
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        
        // Dodaj relację Agent - Queue
        $agent->addQueue($queue);
        
        $scheduleDate = new \DateTime('2025-01-15');
        $timeSlotStart = new \DateTime('10:00');
        $timeSlotEnd = new \DateTime('11:00');
        $entryStatus = 'scheduled';
        
        // Symuluj, że agent jest dostępny
        $this->agentService->expects($this->once())
            ->method('isAgentAvailableAt')
            ->willReturn(true);
        
        // Symuluj, że nie ma konfliktów
        $this->scheduleRepository->expects($this->once())
            ->method('hasAgentScheduleConflict')
            ->willReturn(false);
        
        // Sprawdź czy persist i flush zostały wywołane
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function ($entity) use ($agent, $queue, $scheduleDate, $timeSlotStart, $timeSlotEnd, $entryStatus) {
                $this->assertInstanceOf(Schedule::class, $entity);
                $this->assertSame($agent, $entity->getAgent());
                $this->assertSame($queue, $entity->getQueue());
                $this->assertEquals($scheduleDate, $entity->getScheduleDate());
                $this->assertEquals($timeSlotStart, $entity->getTimeSlotStart());
                $this->assertEquals($timeSlotEnd, $entity->getTimeSlotEnd());
                $this->assertEquals($entryStatus, $entity->getEntryStatus());
            });
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $schedule = $this->scheduleService->createScheduleEntry(
            $agent, $queue, $scheduleDate, $timeSlotStart, $timeSlotEnd, $entryStatus
        );
        
        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame($agent, $schedule->getAgent());
        $this->assertSame($queue, $schedule->getQueue());
        $this->assertEquals($scheduleDate, $schedule->getScheduleDate());
        $this->assertEquals($timeSlotStart, $schedule->getTimeSlotStart());
        $this->assertEquals($timeSlotEnd, $schedule->getTimeSlotEnd());
        $this->assertEquals($entryStatus, $schedule->getEntryStatus());
    }
    
    public function testCreateScheduleEntryThrowsExceptionWhenAgentNotAvailable(): void
    {
        $agent = new Agent();
        $agent->setFullName('Jan Kowalski');
        $agent->setIsActive(true);
        
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        
        // Dodaj relację Agent - Queue
        $agent->addQueue($queue);
        
        $scheduleDate = new \DateTime('2025-01-15');
        $timeSlotStart = new \DateTime('10:00');
        $timeSlotEnd = new \DateTime('11:00');
        $entryStatus = 'scheduled';
        
        // Symuluj, że agent NIE jest dostępny
        $this->agentService->expects($this->once())
            ->method('isAgentAvailableAt')
            ->willReturn(false);
        
        $this->agentService->expects($this->once())
            ->method('getAgentUnavailabilityReason')
            ->willReturn('Już pracuje w tym czasie');
        
        $this->expectException(ConflictHttpException::class);
        $this->scheduleService->createScheduleEntry(
            $agent, $queue, $scheduleDate, $timeSlotStart, $timeSlotEnd, $entryStatus
        );
    }
    
    public function testCreateScheduleEntryThrowsExceptionWhenAgentHasNoSkill(): void
    {
        $agent = new Agent();
        $agent->setFullName('Jan Kowalski');
        $agent->setIsActive(true);
        
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        
        // NIE dodajemy relacji Agent - Queue
        
        $scheduleDate = new \DateTime('2025-01-15');
        $timeSlotStart = new \DateTime('10:00');
        $timeSlotEnd = new \DateTime('11:00');
        $entryStatus = 'scheduled';
        
        $this->expectException(BadRequestHttpException::class);
        $this->scheduleService->createScheduleEntry(
            $agent, $queue, $scheduleDate, $timeSlotStart, $timeSlotEnd, $entryStatus
        );
    }
} 