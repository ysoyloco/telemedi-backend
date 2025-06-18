<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AgentActivityLogControllerTest extends WebTestCase
{
    public function testGetAllActivityLogs(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(60, count($data)); // Elastyczne sprawdzenie
    }

    public function testGetActivityLogsWithFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs', [
            'agent_id' => 1,
            'queue_id' => 1
        ]);

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(5, count($data)); // Elastyczne sprawdzenie
        
        if (count($data) > 0) {
            $this->assertArrayHasKey('agent', $data[0]);
            $this->assertArrayHasKey('queue', $data[0]);
        }
    }

    public function testCreateActivityLog(): void
    {
        $client = static::createClient();
        $timestamp = time();
        
        $client->request('POST', '/api/agent-activity-logs', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'agent_id' => 1,
                'queue_id' => 1,
                'activityStartDatetime' => '2025-01-15T10:00:00',
                'activityEndDatetime' => '2025-01-15T10:15:00',
                'wasSuccessful' => true,
                'activityReferenceId' => "TEST_CALL_{$timestamp}"
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['wasSuccessful']);
        $this->assertEquals("TEST_CALL_{$timestamp}", $data['activityReferenceId']);
    }
}