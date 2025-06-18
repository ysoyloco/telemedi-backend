<?php

namespace App\Tests\Service;

use App\Service\SlotProposalService;
use App\Repository\QueueRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SlotProposalServiceTest extends KernelTestCase
{
    private SlotProposalService $slotProposalService;
    private QueueRepository $queueRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->slotProposalService = static::getContainer()->get(SlotProposalService::class);
        $this->queueRepository = static::getContainer()->get(QueueRepository::class);
    }

    public function testGenerateProposalsForSlot(): void
    {
        // Pobierz kolejkę "Sprzedaż VIP" z fixtures
        $queue = $this->queueRepository->findOneBy(['queueName' => 'Sprzedaż VIP']);
        $this->assertNotNull($queue);

        // Slot w przyszłości - poniedziałek 10:00-11:00
        $slotStart = new \DateTime('next Monday 10:00');
        $slotEnd = new \DateTime('next Monday 11:00');

        // Generuj propozycje
        $result = $this->slotProposalService->generateProposalsForSlot($queue, $slotStart, $slotEnd);

        // Sprawdź strukturę odpowiedzi
        $this->assertArrayHasKey('slot_info', $result);
        $this->assertArrayHasKey('suggested_agents', $result);

        // Sprawdź slot_info
        $slotInfo = $result['slot_info'];
        $this->assertEquals($queue->getId(), $slotInfo['queue_id']);
        $this->assertEquals('Sprzedaż VIP', $slotInfo['queue_name']);

        // Sprawdź suggested_agents
        $suggestedAgents = $result['suggested_agents'];
        $this->assertIsArray($suggestedAgents);
        $this->assertCount(4, $suggestedAgents); // 4 agentów z fixtures

        // Sprawdź strukturę pierwszego agenta
        $firstAgent = $suggestedAgents[0];
        $this->assertArrayHasKey('agent_id', $firstAgent);
        $this->assertArrayHasKey('full_name', $firstAgent);
        $this->assertArrayHasKey('is_available', $firstAgent);
        $this->assertArrayHasKey('availability_conflict_reason', $firstAgent);
        $this->assertArrayHasKey('simple_performance_metric', $firstAgent);

        // Sprawdź że jest Marek Testowy (pierwszy w fixtures)
        $marekAgent = array_filter($suggestedAgents, fn($agent) => $agent['full_name'] === 'Marek Testowy');
        $this->assertCount(1, $marekAgent);
        
        $marek = reset($marekAgent);
        $this->assertTrue($marek['is_available']); // Poniedziałek 10:00 - w jego dostępności
        $this->assertNull($marek['availability_conflict_reason']);
        $this->assertNotEmpty($marek['simple_performance_metric']);
    }

    public function testGenerateProposalsForObslugaKlienta(): void
    {
        // Pobierz kolejkę "Obsługa klienta" z fixtures
        $queue = $this->queueRepository->findOneBy(['queueName' => 'Obsługa klienta']);
        $this->assertNotNull($queue);

        // Slot w przyszłości
        $slotStart = new \DateTime('next Tuesday 14:00');
        $slotEnd = new \DateTime('next Tuesday 15:00');

        $result = $this->slotProposalService->generateProposalsForSlot($queue, $slotStart, $slotEnd);

        $this->assertArrayHasKey('suggested_agents', $result);
        $suggestedAgents = $result['suggested_agents'];
        $this->assertCount(4, $suggestedAgents);

        // Sprawdź że Anna Nowak jest dostępna (wtorek 14:00)
        $annaAgent = array_filter($suggestedAgents, fn($agent) => $agent['full_name'] === 'Anna Nowak');
        $this->assertCount(1, $annaAgent);
        
        $anna = reset($annaAgent);
        $this->assertTrue($anna['is_available']);
    }

    public function testGenerateProposalsForUnavailableTime(): void
    {
        $queue = $this->queueRepository->findOneBy(['queueName' => 'Sprzedaż VIP']);
        
        // Niedziela - nikt nie pracuje
        $slotStart = new \DateTime('next Sunday 10:00');
        $slotEnd = new \DateTime('next Sunday 11:00');

        $result = $this->slotProposalService->generateProposalsForSlot($queue, $slotStart, $slotEnd);
        
        $suggestedAgents = $result['suggested_agents'];
        
        // Wszyscy powinni być niedostępni
        foreach ($suggestedAgents as $agent) {
            $this->assertFalse($agent['is_available']);
            $this->assertNotNull($agent['availability_conflict_reason']);
        }
    }
}
