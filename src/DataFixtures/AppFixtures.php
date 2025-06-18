<?php

namespace App\DataFixtures;

use App\Entity\Agent;
use App\Entity\AgentActivityLog;
use App\Entity\Queue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 2 KOLEJKI
        $queue1 = new Queue();
        $queue1->setQueueName('Sprzedaż VIP');
        $queue1->setPriority(1);
        $queue1->setDescription('Obsługa klientów VIP');
        $queue1->setTargetHandledCallsPerSlot(15);
        $queue1->setTargetSuccessRatePercentage('92.50');
        $manager->persist($queue1);

        $queue2 = new Queue();
        $queue2->setQueueName('Obsługa klienta');
        $queue2->setPriority(2);
        $queue2->setDescription('Standardowa obsługa klientów');
        $queue2->setTargetHandledCallsPerSlot(25);
        $queue2->setTargetSuccessRatePercentage('85.00');
        $manager->persist($queue2);

        // 4 AGENTÓW
        $agent1 = new Agent();
        $agent1->setFullName('Marek Testowy');
        $agent1->setEmail('marek.testowy@example.com');
        $agent1->setDefaultAvailabilityPattern(['Mon' => ['08:00-16:00'], 'Tue' => ['08:00-16:00']]);
        $agent1->setIsActive(true);
        $agent1->addQueue($queue1);
        $agent1->addQueue($queue2);
        $manager->persist($agent1);

        $agent2 = new Agent();
        $agent2->setFullName('Anna Nowak');
        $agent2->setEmail('anna.nowak@example.com');
        $agent2->setDefaultAvailabilityPattern(['Mon' => ['09:00-17:00'], 'Tue' => ['09:00-17:00']]);
        $agent2->setIsActive(true);
        $agent2->addQueue($queue1);
        $agent2->addQueue($queue2);
        $manager->persist($agent2);

        $agent3 = new Agent();
        $agent3->setFullName('Piotr Kowalski');
        $agent3->setEmail('piotr.kowalski@example.com');
        $agent3->setDefaultAvailabilityPattern(['Wed' => ['08:00-16:00'], 'Thu' => ['08:00-16:00']]);
        $agent3->setIsActive(true);
        $agent3->addQueue($queue1);
        $agent3->addQueue($queue2);
        $manager->persist($agent3);

        $agent4 = new Agent();
        $agent4->setFullName('Katarzyna Wiśniewska');
        $agent4->setEmail('katarzyna.wisniewska@example.com');
        $agent4->setDefaultAvailabilityPattern(['Mon' => ['08:00-16:00'], 'Tue' => ['08:00-16:00'], 'Wed' => ['08:00-16:00'], 'Thu' => ['08:00-16:00'], 'Fri' => ['08:00-16:00'], 'Sat' => ['10:00-14:00']]);
        $agent4->setIsActive(true);
        $agent4->addQueue($queue1);
        $agent4->addQueue($queue2);
        $manager->persist($agent4);

        $agent5 = new Agent();
        $agent5->setFullName('Łukasz Weekendowy');
        $agent5->setEmail('lukasz.weekendowy@telemedi.pl');
        $agent5->setDefaultAvailabilityPattern(['Sat' => ['09:00-17:00'], 'Sun' => ['09:00-17:00']]);
        $agent5->setIsActive(true);
        $agent5->addQueue($queue1);
        $agent5->addQueue($queue2);
        $manager->persist($agent5);

        $manager->flush();

        // LOGI - 10 NA KAŻDĄ KOLEJKĘ DLA KAŻDEGO AGENTA (20 LOGÓW NA AGENTA)
        $agents = [$agent1, $agent2, $agent3, $agent4];
        $queues = [$queue1, $queue2];

        foreach ($agents as $agent) {
            foreach ($queues as $queue) {
                for ($i = 1; $i <= 10; $i++) {
                    $log = new AgentActivityLog();
                    $log->setAgent($agent);
                    $log->setQueue($queue);
                    $start = new \DateTime("-{$i} days 10:00");
                    $end = (clone $start)->modify('+15 minutes');
                    $log->setActivityStartDatetime($start);
                    $log->setActivityEndDatetime($end);
                    $log->setWasSuccessful($i % 3 !== 0); // 2/3 successful
                    $log->setActivityReferenceId("CALL_{$agent->getFullName()}_{$queue->getQueueName()}_{$i}");
                    $manager->persist($log);
                }
            }
        }

        // Zmień createScheduleEntries:
        $schedules = [
            ['agent' => 0, 'queue' => 0, 'date' => 'tomorrow', 'start' => '08:00', 'end' => '10:00'],
            ['agent' => 1, 'queue' => 1, 'date' => 'tomorrow', 'start' => '12:00', 'end' => '14:00'],
            ['agent' => 2, 'queue' => 2, 'date' => 'tomorrow', 'start' => '14:00', 'end' => '16:00'],
            ['agent' => 3, 'queue' => 0, 'date' => 'tomorrow', 'start' => '16:00', 'end' => '18:00'],
        ];

        $manager->flush();
    }
}