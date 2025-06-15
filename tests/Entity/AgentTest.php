<?php

namespace App\Tests\Entity;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Entity\Schedule;
use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
{
    private Agent $agent;

    protected function setUp(): void
    {
        $this->agent = new Agent();
    }

    public function testGettersAndSetters(): void
    {
        $this->agent->setFullName('Jan Kowalski');
        $this->assertEquals('Jan Kowalski', $this->agent->getFullName());

        $this->agent->setEmail('jan.kowalski@example.com');
        $this->assertEquals('jan.kowalski@example.com', $this->agent->getEmail());

        $this->agent->setIsActive(true);
        $this->assertTrue($this->agent->isIsActive());

        $defaultPattern = [
            'Mon' => ['08:00-16:00'],
            'Tue' => ['08:00-16:00'],
            'Wed' => ['08:00-16:00'],
            'Thu' => ['08:00-16:00'],
            'Fri' => ['08:00-16:00']
        ];
        $this->agent->setDefaultAvailabilityPattern($defaultPattern);
        $this->assertEquals($defaultPattern, $this->agent->getDefaultAvailabilityPattern());
    }

    public function testQueueRelation(): void
    {
        $queue = new Queue();
        $queue->setQueueName('Test Queue');

        $this->agent->addQueue($queue);
        $this->assertCount(1, $this->agent->getQueues());
        $this->assertTrue($this->agent->getQueues()->contains($queue));

        $this->agent->removeQueue($queue);
        $this->assertCount(0, $this->agent->getQueues());
        $this->assertFalse($this->agent->getQueues()->contains($queue));
    }

    public function testScheduleRelation(): void
    {
        $schedule = new Schedule();
        
        $this->agent->addSchedule($schedule);
        $this->assertCount(1, $this->agent->getSchedules());
        $this->assertTrue($this->agent->getSchedules()->contains($schedule));
        $this->assertSame($this->agent, $schedule->getAgent());

        $this->agent->removeSchedule($schedule);
        $this->assertCount(0, $this->agent->getSchedules());
        $this->assertFalse($this->agent->getSchedules()->contains($schedule));
    }
} 