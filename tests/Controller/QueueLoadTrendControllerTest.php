<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class QueueLoadTrendControllerTest extends WebTestCase
{
    public function testGetAllTrends(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queue-load-trends');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        
        if (count($responseData) > 0) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('year', $responseData[0]);
            $this->assertArrayHasKey('quarter', $responseData[0]);
            $this->assertArrayHasKey('metricName', $responseData[0]);
            $this->assertArrayHasKey('metricValue', $responseData[0]);
            $this->assertArrayHasKey('queue', $responseData[0]);
        }
    }

    public function testGetTrendsWithFilters(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queue-load-trends?queue_id=1&year=2025&quarter=1&metric_name=average_call_time');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        
        // Jeśli są wyniki, sprawdź czy filtry działają poprawnie
        if (count($responseData) > 0) {
            $this->assertEquals(1, $responseData[0]['queue']['id']);
            $this->assertEquals(2025, $responseData[0]['year']);
            $this->assertEquals(1, $responseData[0]['quarter']);
            $this->assertEquals('average_call_time', $responseData[0]['metricName']);
        }
    }

    public function testGetSingleTrend(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queue-load-trends/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals(1, $responseData['id']);
        $this->assertArrayHasKey('year', $responseData);
        $this->assertArrayHasKey('quarter', $responseData);
        $this->assertArrayHasKey('metricName', $responseData);
        $this->assertArrayHasKey('metricValue', $responseData);
    }

    public function testGetTrendNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queue-load-trends/9999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCreateTrend(): void
    {
        $client = static::createClient();
        $postData = [
            'queue_id' => 1,
            'year' => 2025,
            'quarter' => 4,
            'metricName' => 'success_rate_percentage',
            'metricValue' => '88.5',
            'calculationDate' => '2025-10-15T00:00:00',
            'additionalDescription' => 'Test metric description'
        ];

        $client->request(
            'POST',
            '/api/queue-load-trends',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($postData['year'], $responseData['year']);
        $this->assertEquals($postData['quarter'], $responseData['quarter']);
        $this->assertEquals($postData['metricName'], $responseData['metricName']);
        $this->assertEquals($postData['metricValue'], $responseData['metricValue']);
    }

    public function testCreateTrendMissingRequiredData(): void
    {
        $client = static::createClient();
        $postData = [
            'year' => 2025,
            'quarter' => 4,
            // Brak queue_id, metricName i metricValue
        ];

        $client->request(
            'POST',
            '/api/queue-load-trends',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testUpdateTrend(): void
    {
        $client = static::createClient();
        $trendId = 1;
        $putData = [
            'metricValue' => '93.2',
            'additionalDescription' => 'Updated test description'
        ];

        $client->request(
            'PUT',
            '/api/queue-load-trends/' . $trendId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($trendId, $responseData['id']);
        $this->assertEquals($putData['metricValue'], $responseData['metricValue']);
        $this->assertEquals($putData['additionalDescription'], $responseData['additionalDescription']);
    }

    public function testUpdateTrendNotFound(): void
    {
        $client = static::createClient();
        $trendId = 9999;
        $putData = ['metricValue' => '95.5'];

        $client->request(
            'PUT',
            '/api/queue-load-trends/' . $trendId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteTrend(): void
    {
        $client = static::createClient();
        $trendId = 1;

        $client->request('DELETE', '/api/queue-load-trends/' . $trendId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteTrendNotFound(): void
    {
        $client = static::createClient();
        $trendId = 9999;

        $client->request('DELETE', '/api/queue-load-trends/' . $trendId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetQueueMetricsAnalytics(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queue-load-trends/analytics/queue/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);

        $this->assertArrayHasKey('queue', $responseData);
        $this->assertArrayHasKey('metrics', $responseData);
        
        $this->assertEquals(1, $responseData['queue']['id']);
        
        // Sprawdź strukturę metryk
        if (count($responseData['metrics']) > 0) {
            $firstMetric = array_key_first($responseData['metrics']);
            $this->assertArrayHasKey('description', $responseData['metrics'][$firstMetric]);
            $this->assertArrayHasKey('values', $responseData['metrics'][$firstMetric]);
            $this->assertIsArray($responseData['metrics'][$firstMetric]['values']);
        }
    }

    public function testGetQueueMetricsAnalyticsNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/queue-load-trends/analytics/queue/9999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
} 