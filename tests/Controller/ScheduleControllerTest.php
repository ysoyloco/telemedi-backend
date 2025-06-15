<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ScheduleControllerTest extends WebTestCase
{
    public function testGetCalendarView(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/calendar-view?queue_id=1&start_date=2025-06-10&end_date=2025-06-16');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('queue_info', $responseData);
        $this->assertEquals(1, $responseData['queue_info']['id']);
        $this->assertArrayHasKey('schedule_entries', $responseData);
        $this->assertIsArray($responseData['schedule_entries']);
        // Further checks on schedule_entries structure if needed
    }

    public function testGetSlotProposals(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/slot-proposals?queue_id=1&slot_start_datetime=2025-06-10T09:00:00Z&slot_end_datetime=2025-06-10T10:00:00Z');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('slot_info', $responseData);
        $this->assertEquals(1, $responseData['slot_info']['queue_id']);
        $this->assertArrayHasKey('suggested_agents', $responseData);
        $this->assertIsArray($responseData['suggested_agents']);
        // Further checks on suggested_agents structure if needed
    }

    public function testCreateScheduleEntry(): void
    {
        $client = static::createClient();
        $postData = [
            'agent_id' => 1,
            'queue_id' => 1,
            'schedule_date' => '2025-06-12',
            'time_slot_start' => '14:00:00',
            'time_slot_end' => '15:00:00',
            'entry_status' => 'Zaproponowany'
        ];

        $client->request(
            'POST',
            '/api/schedules',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($postData['agent_id'], $responseData['agent_id']);
        $this->assertEquals($postData['queue_id'], $responseData['queue_id']);
        $this->assertEquals($postData['schedule_date'], $responseData['schedule_date']);
    }

    public function testUpdateScheduleEntry(): void
    {
        $client = static::createClient();
        $scheduleEntryId = 101; // Assuming this ID exists from mock data
        $putData = [
            'agent_id' => 2,
            'entry_status' => 'Potwierdzony_Przez_Managera'
        ];

        $client->request(
            'PUT',
            '/api/schedules/' . $scheduleEntryId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($scheduleEntryId, $responseData['id']);
        $this->assertEquals($putData['agent_id'], $responseData['agent_id']);
        $this->assertEquals($putData['entry_status'], $responseData['entry_status']);
    }
    
    public function testUpdateScheduleEntryNotFound(): void
    {
        $client = static::createClient();
        $scheduleEntryId = 9999; // Non-existent ID
        $putData = ['entry_status' => 'Anulowany'];

        $client->request(
            'PUT',
            '/api/schedules/' . $scheduleEntryId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($putData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteScheduleEntry(): void
    {
        $client = static::createClient();
        $scheduleEntryId = 102; // Assuming this ID exists initially from mock data

        $client->request('DELETE', '/api/schedules/' . $scheduleEntryId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
    
    public function testDeleteScheduleEntryNotFound(): void
    {
        $client = static::createClient();
        $scheduleEntryId = 9999; // Non-existent ID

        $client->request('DELETE', '/api/schedules/' . $scheduleEntryId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGenerateScheduleProposals(): void
    {
        $client = static::createClient();
        $postData = [
            'start_date' => '2025-06-20',
            'end_date' => '2025-06-22',
            'queue_ids' => [1, 3]
        ];

        $client->request(
            'POST',
            '/api/schedules/generate',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($postData)
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        // Check structure of the first generated entry
        if (count($responseData) > 0) {
            $this->assertArrayHasKey('id', $responseData[0]);
            $this->assertArrayHasKey('agent_id', $responseData[0]);
            $this->assertArrayHasKey('queue_id', $responseData[0]);
            $this->assertEquals('Zaproponowany_Systemowo', $responseData[0]['entry_status']);
        }
    }
} 