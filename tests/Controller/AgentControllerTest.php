<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AgentControllerTest extends WebTestCase
{
    public function testGetAllAgents(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Sprawdź strukturę pierwszego elementu
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('fullName', $responseData[0]);
        $this->assertArrayHasKey('email', $responseData[0]);
        $this->assertArrayHasKey('defaultAvailabilityPattern', $responseData[0]);
        $this->assertArrayHasKey('isActive', $responseData[0]);
        $this->assertArrayHasKey('queues', $responseData[0]);
    }

    public function testGetSingleAgent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(1, $responseData['id']);
        $this->assertArrayHasKey('fullName', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('defaultAvailabilityPattern', $responseData);
        $this->assertArrayHasKey('isActive', $responseData);
    }

    public function testGetAgentNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/9999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateAgent(): void
    {
        $client = static::createClient();
        $postData = [
            'fullName' => 'Marek Testowy',
            'email' => 'marek.testowy@example.com',
            'defaultAvailabilityPattern' => [
                'Mon' => ['09:00-17:00'],
                'Tue' => ['09:00-17:00'],
                'Wed' => ['09:00-17:00'],
                'Thu' => ['09:00-17:00'],
                'Fri' => ['09:00-17:00']
            ],
            'isActive' => true,
            'queues' => [1, 2]
        ];

        $client->request(
            'POST',
            '/api/agents',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($postData['fullName'], $responseData['fullName']);
        $this->assertEquals($postData['email'], $responseData['email']);
        $this->assertEquals($postData['defaultAvailabilityPattern'], $responseData['defaultAvailabilityPattern']);
        $this->assertEquals($postData['isActive'], $responseData['isActive']);
    }

    public function testUpdateAgent(): void
    {
        $client = static::createClient();
        $agentId = 1; // Zakładamy, że ID 1 istnieje w bazie
        $putData = [
            'fullName' => 'Jan Kowalski Zmodyfikowany',
            'email' => 'jan.kowalski.zmod@example.com'
        ];

        $client->request(
            'PUT',
            '/api/agents/' . $agentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($agentId, $responseData['id']);
        $this->assertEquals($putData['fullName'], $responseData['fullName']);
        $this->assertEquals($putData['email'], $responseData['email']);
    }

    public function testUpdateAgentNotFound(): void
    {
        $client = static::createClient();
        $agentId = 9999; // ID, które nie istnieje
        $putData = ['fullName' => 'Nieistniejący Agent'];

        $client->request(
            'PUT',
            '/api/agents/' . $agentId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteAgent(): void
    {
        $client = static::createClient();
        $agentId = 1; // Zakładamy, że ID 1 istnieje w bazie

        $client->request('DELETE', '/api/agents/' . $agentId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteAgentNotFound(): void
    {
        $client = static::createClient();
        $agentId = 9999; // ID, które nie istnieje

        $client->request('DELETE', '/api/agents/' . $agentId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetAgentQueues(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/1/queues');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        // Sprawdzenie struktury kolejek, zakładając że agent ma przypisane kolejki
        if (count($responseData) > 0) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('queueName', $responseData[0]);
            $this->assertArrayHasKey('priority', $responseData[0]);
        }
    }

    public function testGetAvailableAgentsForPeriod(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/available/for-period?start_date=2025-06-20T09:00:00Z&end_date=2025-06-20T10:00:00Z&queue_id=1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        // Sprawdzenie struktury agentów dostępnych w danym okresie
        if (count($responseData) > 0) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('fullName', $responseData[0]);
            $this->assertArrayHasKey('email', $responseData[0]);
        }
    }
} 