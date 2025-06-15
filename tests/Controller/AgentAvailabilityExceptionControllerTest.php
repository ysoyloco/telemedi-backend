<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AgentAvailabilityExceptionControllerTest extends WebTestCase
{
    public function testGetAllExceptions(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-availability-exceptions');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        
        if (count($responseData) > 0) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('unavailableDatetimeStart', $responseData[0]);
            $this->assertArrayHasKey('unavailableDatetimeEnd', $responseData[0]);
            $this->assertArrayHasKey('agent', $responseData[0]);
        }
    }

    public function testGetExceptionsWithFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-availability-exceptions?agent_id=1&start_date=2025-01-01&end_date=2025-12-31');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
    }

    public function testGetSingleException(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-availability-exceptions/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(1, $responseData['id']);
        $this->assertArrayHasKey('unavailableDatetimeStart', $responseData);
        $this->assertArrayHasKey('unavailableDatetimeEnd', $responseData);
        $this->assertArrayHasKey('agent', $responseData);
    }

    public function testGetExceptionNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-availability-exceptions/9999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateException(): void
    {
        $client = static::createClient();
        $postData = [
            'agent_id' => 1,
            'unavailableDatetimeStart' => '2025-07-10T00:00:00',
            'unavailableDatetimeEnd' => '2025-07-17T23:59:59'
        ];

        $client->request(
            'POST',
            '/api/agent-availability-exceptions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertStringContainsString('2025-07-10', $responseData['unavailableDatetimeStart']);
        $this->assertStringContainsString('2025-07-17', $responseData['unavailableDatetimeEnd']);
    }

    public function testCreateExceptionMissingRequiredData(): void
    {
        $client = static::createClient();
        $postData = [
            'unavailableDatetimeStart' => '2025-07-10T00:00:00',
            'unavailableDatetimeEnd' => '2025-07-17T23:59:59'
        ];

        $client->request(
            'POST',
            '/api/agent-availability-exceptions',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUpdateException(): void
    {
        $client = static::createClient();
        $exceptionId = 1;
        $putData = [
            'unavailableDatetimeEnd' => '2025-07-20T23:59:59'
        ];

        $client->request(
            'PUT',
            '/api/agent-availability-exceptions/' . $exceptionId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($exceptionId, $responseData['id']);
        $this->assertStringContainsString('2025-07-20', $responseData['unavailableDatetimeEnd']);
    }

    public function testUpdateExceptionNotFound(): void
    {
        $client = static::createClient();
        $exceptionId = 9999;
        $putData = ['unavailableDatetimeEnd' => '2025-07-20T23:59:59'];

        $client->request(
            'PUT',
            '/api/agent-availability-exceptions/' . $exceptionId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteException(): void
    {
        $client = static::createClient();
        $exceptionId = 1;

        $client->request('DELETE', '/api/agent-availability-exceptions/' . $exceptionId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteExceptionNotFound(): void
    {
        $client = static::createClient();
        $exceptionId = 9999;

        $client->request('DELETE', '/api/agent-availability-exceptions/' . $exceptionId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCheckAvailability(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-availability-exceptions/check/availability?agent_id=1&datetime=2025-06-15T09:00:00');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('agent_id', $responseData);
        $this->assertArrayHasKey('datetime', $responseData);
        $this->assertArrayHasKey('is_available', $responseData);
        $this->assertIsBool($responseData['is_available']);
    }

    public function testCheckAvailabilityMissingParameters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agent-availability-exceptions/check/availability?agent_id=1'); // Brak parametru datetime

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
} 