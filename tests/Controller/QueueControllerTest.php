<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QueueControllerTest extends WebTestCase
{
    public function testGetAllQueues(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queues');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(2, $data); // 2 kolejki z fixtures
        
        // Sprawdź sortowanie po priorytecie
        $this->assertEquals('Sprzedaż VIP', $data[0]['queue_name']);
        $this->assertEquals(1, $data[0]['priority']);
        
        $this->assertEquals('Obsługa klienta', $data[1]['queue_name']);
        $this->assertEquals(2, $data[1]['priority']);
    }

    public function testQueueStructure(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queues');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $queue = $data[0];
        
        $this->assertArrayHasKey('id', $queue);
        $this->assertArrayHasKey('queue_name', $queue);
        $this->assertArrayHasKey('priority', $queue);
        $this->assertArrayHasKey('target_handled_calls_per_slot', $queue);
        $this->assertArrayHasKey('target_success_rate_percentage', $queue);
        
        $this->assertEquals(15, $queue['target_handled_calls_per_slot']);
        $this->assertEquals(92.50, $queue['target_success_rate_percentage']);
    }
}