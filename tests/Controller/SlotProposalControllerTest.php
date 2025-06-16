<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SlotProposalControllerTest extends WebTestCase
{
    public function testPredykcjaDlaObslugiKlienta()
    {
        $client = static::createClient();

        // Slot w godzinach pracy, bez urlopów
        $client->request('GET', '/api/slot-proposals', [
            'queue_id' => 1, // Obsługa klienta (z fixtures)
            'slot_start_datetime' => (new \DateTime('+2 days 10:00'))->format('Y-m-d\TH:i:s'),
            'slot_end_datetime' => (new \DateTime('+2 days 11:00'))->format('Y-m-d\TH:i:s'),
        ]);

        $this->assertResponseIsSuccessful();
        $data = $client->getResponse()->toArray();

        // Najlepszy agent powinien być Jan Kowalski (customer_service_expert)
        $this->assertEquals('Jan Kowalski', $data['suggested_agents'][0]['full_name']);
        $this->assertTrue($data['suggested_agents'][0]['is_available']);
        // Najsłabszy agent (Maria) powinna być na końcu
        $last = end($data['suggested_agents']);
        $this->assertEquals('Maria Nowicjusz', $last['full_name']);
    }

    public function testPredykcjaDlaWsparciaTechnicznego()
    {
        $client = static::createClient();

        $client->request('GET', '/api/slot-proposals', [
            'queue_id' => 2, // Wsparcie techniczne
            'slot_start_datetime' => (new \DateTime('+2 days 11:00'))->format('Y-m-d\TH:i:s'),
            'slot_end_datetime' => (new \DateTime('+2 days 12:00'))->format('Y-m-d\TH:i:s'),
        ]);

        $this->assertResponseIsSuccessful();
        $data = $client->getResponse()->toArray();

        // Najlepsza powinna być Anna Technow (tech_expert)
        $this->assertEquals('Anna Technow', $data['suggested_agents'][0]['full_name']);
        $this->assertTrue($data['suggested_agents'][0]['is_available']);
    }

    public function testPredykcjaDlaSprzedazy()
    {
        $client = static::createClient();

        $client->request('GET', '/api/slot-proposals', [
            'queue_id' => 3, // Sprzedaż
            'slot_start_datetime' => (new \DateTime('+2 days 13:00'))->format('Y-m-d\TH:i:s'),
            'slot_end_datetime' => (new \DateTime('+2 days 14:00'))->format('Y-m-d\TH:i:s'),
        ]);

        $this->assertResponseIsSuccessful();
        $data = $client->getResponse()->toArray();

        // Najlepszy powinien być Tomasz Sprzedawca (sales_expert)
        $this->assertEquals('Tomasz Sprzedawca', $data['suggested_agents'][0]['full_name']);
        $this->assertTrue($data['suggested_agents'][0]['is_available']);
    }

    public function testAgentNaUrlopieNieJestDostepny()
    {
        $client = static::createClient();

        // Jan Kowalski ma urlop za 5 dni (patrz fixtures)
        $client->request('GET', '/api/slot-proposals', [
            'queue_id' => 1,
            'slot_start_datetime' => (new \DateTime('+6 days 10:00'))->format('Y-m-d\TH:i:s'),
            'slot_end_datetime' => (new \DateTime('+6 days 11:00'))->format('Y-m-d\TH:i:s'),
        ]);

        $this->assertResponseIsSuccessful();
        $data = $client->getResponse()->toArray();

        // Jan Kowalski powinien być niedostępny
        foreach ($data['suggested_agents'] as $agent) {
            if ($agent['full_name'] === 'Jan Kowalski') {
                $this->assertFalse($agent['is_available']);
                $this->assertEquals('Urlop lub inna nieobecność', $agent['availability_conflict_reason']);
            }
        }
    }
}