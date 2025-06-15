<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AgentActivityLogControllerTest extends WebTestCase
{
    public function testGetAllActivityLogs(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        
        if (count($responseData) > 0) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('activityStartDatetime', $responseData[0]);
            $this->assertArrayHasKey('wasSuccessful', $responseData[0]);
            $this->assertArrayHasKey('agent', $responseData[0]);
            $this->assertArrayHasKey('queue', $responseData[0]);
        }
    }

    public function testGetActivityLogsWithFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs?agent_id=1&queue_id=1&start_date=2025-01-01&end_date=2025-12-31');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
    }

    public function testGetSingleActivityLog(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(1, $responseData['id']);
        $this->assertArrayHasKey('activityStartDatetime', $responseData);
        $this->assertArrayHasKey('wasSuccessful', $responseData);
    }

    public function testGetActivityLogNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs/9999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateActivityLog(): void
    {
        $client = static::createClient();
        $postData = [
            'agent_id' => 1,
            'queue_id' => 1,
            'activityStartDatetime' => '2025-06-15T09:30:00',
            'activityEndDatetime' => '2025-06-15T09:45:00',
            'wasSuccessful' => true,
            'activityReferenceId' => 'TEST-123'
        ];

        $client->request(
            'POST',
            '/api/agent-activity-logs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($postData['wasSuccessful'], $responseData['wasSuccessful']);
        $this->assertEquals($postData['activityReferenceId'], $responseData['activityReferenceId']);
    }

    public function testCreateActivityLogMissingRequiredData(): void
    {
        $client = static::createClient();
        $postData = [
            'activityStartDatetime' => '2025-06-15T09:30:00',
            'wasSuccessful' => true
        ];

        $client->request(
            'POST',
            '/api/agent-activity-logs',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUpdateActivityLog(): void
    {
        $client = static::createClient();
        $logId = 1;
        $putData = [
            'wasSuccessful' => false,
            'activityEndDatetime' => '2025-06-15T10:15:00'
        ];

        $client->request(
            'PUT',
            '/api/agent-activity-logs/' . $logId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($logId, $responseData['id']);
        $this->assertEquals($putData['wasSuccessful'], $responseData['wasSuccessful']);
    }

    public function testUpdateActivityLogNotFound(): void
    {
        $client = static::createClient();
        $logId = 9999;
        $putData = ['wasSuccessful' => false];

        $client->request(
            'PUT',
            '/api/agent-activity-logs/' . $logId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteActivityLog(): void
    {
        $client = static::createClient();
        $logId = 1;

        $client->request('DELETE', '/api/agent-activity-logs/' . $logId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteActivityLogNotFound(): void
    {
        $client = static::createClient();
        $logId = 9999;

        $client->request('DELETE', '/api/agent-activity-logs/' . $logId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetAgentAnalytics(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-activity-logs/analytics/agent/1?start_date=2025-01-01&end_date=2025-12-31');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('agent', $responseData);
        $this->assertArrayHasKey('period', $responseData);
        $this->assertArrayHasKey('overall', $responseData);
        $this->assertArrayHasKey('queues', $responseData);

        $this->assertArrayHasKey('totalActivities', $responseData['overall']);
        $this->assertArrayHasKey('successRate', $responseData['overall']);
        $this->assertArrayHasKey('avgDuration', $responseData['overall']);
    }
} 