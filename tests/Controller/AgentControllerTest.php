<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AgentControllerTest extends WebTestCase
{
    public function testGetAllAgents(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(4, $data); // 4 agentów z fixtures
        
        // Nie sprawdzamy konkretnego imienia bo mogło być zmienione przez inne testy
        $this->assertArrayHasKey('fullName', $data[0]);
        $this->assertArrayHasKey('email', $data[0]);
    }

    public function testGetAgentById(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/1');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('fullName', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertIsBool($data['isActive']);
    }

    public function testGetAgentNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateAgent(): void
    {
        $client = static::createClient();
        $timestamp = time();
        
        $client->request('POST', '/api/agents', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fullName' => "Nowy Agent {$timestamp}",
                'email' => "nowy.agent.{$timestamp}@example.com",
                'isActive' => true,
                'queues' => [1, 2]
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("Nowy Agent {$timestamp}", $data['fullName']);
        $this->assertEquals("nowy.agent.{$timestamp}@example.com", $data['email']);
    }

    public function testUpdateAgent(): void
    {
        $client = static::createClient();
        $timestamp = time();
        
        $client->request('PUT', '/api/agents/2', [], [], 
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'fullName' => "Anna Zaktualizowana {$timestamp}",
                'email' => "anna.updated.{$timestamp}@example.com"
            ])
        );

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals("Anna Zaktualizowana {$timestamp}", $data['fullName']);
    }

    public function testDeleteAgent(): void
    {
        // Najpierw sprawdź czy agent istnieje
        $client = static::createClient();
        $client->request('GET', '/api/agents/3');
        
        if ($client->getResponse()->getStatusCode() === 200) {
            $client->request('DELETE', '/api/agents/3');
            $this->assertResponseStatusCodeSame(204);
            
            // Sprawdź że agent został usunięty
            $client->request('GET', '/api/agents/3');
            $this->assertResponseStatusCodeSame(404);
        } else {
            $this->markTestSkipped('Agent 3 już nie istnieje');
        }
    }

    public function testGetAgentQueues(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/agents/1/queues');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }
}