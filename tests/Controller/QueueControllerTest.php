<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class QueueControllerTest extends WebTestCase
{
    public function testGetQueues(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queues');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Check the structure of the first element (assuming it exists)
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('queue_name', $responseData[0]);
        $this->assertArrayHasKey('priority', $responseData[0]);
        $this->assertArrayHasKey('target_handled_calls_per_slot', $responseData[0]);
        $this->assertArrayHasKey('target_success_rate_percentage', $responseData[0]);

        // Example: Check specific mock data if needed (adjust if mock data changes)
        $expectedFirstQueue = [
            'id' => 1,
            'queue_name' => 'Sprzedaż VIP',
            'priority' => 1,
            'target_handled_calls_per_slot' => 15,
            'target_success_rate_percentage' => '92.50',
        ];
        $this->assertContains($expectedFirstQueue, $responseData, 'The expected first queue was not found in the response.');
    }

    public function testGetQueuesSorted(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queues?sort_by=priority:desc');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(3, $responseData[0]['id']); // Assuming Reklamacje (id 3, prio 3) is now first
    }
} 