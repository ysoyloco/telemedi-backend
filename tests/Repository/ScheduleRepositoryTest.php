<?php

namespace App\Tests\Repository;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Entity\Schedule;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ScheduleRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ScheduleRepository $repository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->repository = $this->entityManager->getRepository(Schedule::class);
    }

    public function testFindByQueueAndDateRange(): void
    {
        // Przygotowanie danych testowych
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        $this->entityManager->persist($queue);

        $agent = new Agent();
        $agent->setFullName('Test Agent');
        $agent->setIsActive(true);
        $agent->addQueue($queue);
        $this->entityManager->persist($agent);

        $startDate = new \DateTime('2025-01-01');
        $endDate = new \DateTime('2025-01-31');
        
        // Dodaj schedule w zakresie
        $schedule1 = new Schedule();
        $schedule1->setAgent($agent);
        $schedule1->setQueue($queue);
        $schedule1->setScheduleDate(new \DateTime('2025-01-15'));
        $schedule1->setTimeSlotStart(new \DateTime('10:00'));
        $schedule1->setTimeSlotEnd(new \DateTime('11:00'));
        $schedule1->setEntryStatus('scheduled');
        $this->entityManager->persist($schedule1);
        
        // Dodaj schedule poza zakresem
        $schedule2 = new Schedule();
        $schedule2->setAgent($agent);
        $schedule2->setQueue($queue);
        $schedule2->setScheduleDate(new \DateTime('2025-02-15'));
        $schedule2->setTimeSlotStart(new \DateTime('10:00'));
        $schedule2->setTimeSlotEnd(new \DateTime('11:00'));
        $schedule2->setEntryStatus('scheduled');
        $this->entityManager->persist($schedule2);
        
        $this->entityManager->flush();

        // Testuj metodę
        $results = $this->repository->findByQueueAndDateRange($queue, $startDate, $endDate);
        
        $this->assertCount(1, $results);
        $this->assertEquals($schedule1->getId(), $results[0]->getId());
    }

    public function testHasAgentScheduleConflict(): void
    {
        // Przygotowanie danych testowych
        $queue = new Queue();
        $queue->setQueueName('Test Queue');
        $this->entityManager->persist($queue);

        $agent = new Agent();
        $agent->setFullName('Test Agent');
        $agent->setIsActive(true);
        $agent->addQueue($queue);
        $this->entityManager->persist($agent);

        $scheduleDate = new \DateTime('2025-01-15');
        $timeSlotStart = new \DateTime('10:00');
        $timeSlotEnd = new \DateTime('11:00');
        
        // Dodaj schedule w tym samym czasie
        $schedule = new Schedule();
        $schedule->setAgent($agent);
        $schedule->setQueue($queue);
        $schedule->setScheduleDate(clone $scheduleDate);
        $schedule->setTimeSlotStart(clone $timeSlotStart);
        $schedule->setTimeSlotEnd(clone $timeSlotEnd);
        $schedule->setEntryStatus('scheduled');
        $this->entityManager->persist($schedule);
        
        $this->entityManager->flush();

        // Testuj metodę - powinien być konflikt
        $hasConflict = $this->repository->hasAgentScheduleConflict($agent, $scheduleDate, $timeSlotStart, $timeSlotEnd);
        $this->assertTrue($hasConflict);

        // Testuj z wykluczonym ID - nie powinno być konfliktu
        $hasConflictExcluded = $this->repository->hasAgentScheduleConflict($agent, $scheduleDate, $timeSlotStart, $timeSlotEnd, $schedule->getId());
        $this->assertFalse($hasConflictExcluded);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Czyszczenie po testach
        $this->entityManager->close();
    }
} 