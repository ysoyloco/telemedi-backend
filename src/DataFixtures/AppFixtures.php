<?php

namespace App\DataFixtures;

use App\Entity\Agent;
use App\Entity\AgentActivityLog;
use App\Entity\AgentAvailabilityException;
use App\Entity\Queue;
use App\Entity\QueueLoadTrend;
use App\Entity\Schedule;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. KOLEJKI z realistycznymi celami
        $queues = [
            [
                'name' => 'Obsługa klienta',
                'priority' => 1,
                'description' => 'Podstawowa obsługa klientów, pytania o produkty',
                'target_calls' => 25,
                'target_success' => 85.00
            ],
            [
                'name' => 'Wsparcie techniczne',
                'priority' => 2, 
                'description' => 'Pomoc techniczna, rozwiązywanie problemów',
                'target_calls' => 15,
                'target_success' => 75.00
            ],
            [
                'name' => 'Sprzedaż',
                'priority' => 3,
                'description' => 'Sprzedaż produktów i usług',
                'target_calls' => 20,
                'target_success' => 60.00
            ],
            [
                'name' => 'Reklamacje',
                'priority' => 4,
                'description' => 'Obsługa reklamacji i zwrotów',
                'target_calls' => 12,
                'target_success' => 70.00
            ]
        ];

        $queueEntities = [];
        foreach ($queues as $queueData) {
            $queue = new Queue();
            $queue->setQueueName($queueData['name']);
            $queue->setPriority($queueData['priority']);
            $queue->setDescription($queueData['description']);
            $queue->setTargetHandledCallsPerSlot($queueData['target_calls']);
            $queue->setTargetSuccessRatePercentage($queueData['target_success']);
            $manager->persist($queue);
            $queueEntities[] = $queue;
        }

        // 2. AGENCI z różnymi profilami
        $agents = [
            [
                'name' => 'Jan Kowalski',
                'email' => 'jan.kowalski@telemedi.pl',
                'pattern' => [
                    'Mon' => ['08:00-16:00'],
                    'Tue' => ['08:00-16:00'],
                    'Wed' => ['08:00-16:00'],
                    'Thu' => ['08:00-16:00'],
                    'Fri' => ['08:00-16:00']
                ],
                'profile' => 'customer_service_expert', // Expert w obsłudze klienta
                'queues' => [0, 1] // Obsługa klienta + Wsparcie techniczne
            ],
            [
                'name' => 'Anna Technow',
                'email' => 'anna.technow@telemedi.pl',
                'pattern' => [
                    'Mon' => ['09:00-17:00'],
                    'Tue' => ['09:00-17:00'],
                    'Wed' => ['09:00-17:00'],
                    'Thu' => ['09:00-17:00'],
                    'Fri' => ['09:00-17:00']
                ],
                'profile' => 'tech_expert', // Expert w wsparciu technicznym
                'queues' => [1, 3] // Wsparcie techniczne + Reklamacje
            ],
            [
                'name' => 'Piotr Uniwersalny',
                'email' => 'piotr.uniwersalny@telemedi.pl',
                'pattern' => [
                    'Mon' => ['07:00-15:00'],
                    'Tue' => ['07:00-15:00'],
                    'Wed' => ['07:00-15:00'],
                    'Thu' => ['07:00-15:00'],
                    'Fri' => ['07:00-15:00']
                ],
                'profile' => 'universal', // Średni wszędzie
                'queues' => [0, 1, 2, 3] // Wszystkie kolejki
            ],
            [
                'name' => 'Tomasz Sprzedawca',
                'email' => 'tomasz.sprzedawca@telemedi.pl',
                'pattern' => [
                    'Mon' => ['10:00-18:00'],
                    'Tue' => ['10:00-18:00'],
                    'Wed' => ['10:00-18:00'],
                    'Thu' => ['10:00-18:00'],
                    'Fri' => ['10:00-18:00']
                ],
                'profile' => 'sales_expert', // Expert w sprzedaży
                'queues' => [2, 0] // Sprzedaż + Obsługa klienta
            ],
            [
                'name' => 'Maria Nowicjusz',
                'email' => 'maria.nowicjusz@telemedi.pl',
                'pattern' => [
                    'Mon' => ['08:00-16:00'],
                    'Tue' => ['08:00-16:00'],
                    'Wed' => ['08:00-16:00'],
                    'Thu' => ['08:00-16:00'],
                    'Fri' => ['08:00-16:00']
                ],
                'profile' => 'junior', // Słaba wszędzie (nowa)
                'queues' => [0, 2] // Obsługa klienta + Sprzedaż
            ]
        ];

        $agentEntities = [];
        foreach ($agents as $agentData) {
            $agent = new Agent();
            $agent->setFullName($agentData['name']);
            $agent->setEmail($agentData['email']);
            $agent->setDefaultAvailabilityPattern($agentData['pattern']);
            $agent->setIsActive(true);
            
            // Przypisz kolejki
            foreach ($agentData['queues'] as $queueIndex) {
                $agent->addQueue($queueEntities[$queueIndex]);
            }
            
            $manager->persist($agent);
            $agentEntities[] = ['entity' => $agent, 'profile' => $agentData['profile']];
        }

        $manager->flush();

        // 3. ACTIVITY LOGI - realistyczne dane pokazujące różnice w wydajności
        $this->createRealisticActivityLogs($manager, $agentEntities, $queueEntities);

        // 4. WYJĄTKI DOSTĘPNOŚCI (urlopy)
        $this->createAvailabilityExceptions($manager, $agentEntities);

        // 5. PRZYKŁADOWE WPISY W GRAFIKU na przyszłość
        $this->createScheduleEntries($manager, $agentEntities, $queueEntities);

        // 6. TRENDY OBCIĄŻENIA KOLEJEK
        $this->createQueueLoadTrends($manager, $queueEntities);

        $manager->flush();
    }

    private function createRealisticActivityLogs(ObjectManager $manager, array $agentEntities, array $queueEntities): void
    {
        $startDate = new \DateTime('-60 days');
        $endDate = new \DateTime('-1 day');
        
        // Profile skuteczności agentów w różnych kolejkach
        $profiles = [
            'customer_service_expert' => [
                0 => ['success_rate' => 0.95, 'avg_duration' => 8], // Obsługa klienta - ŚWIETNY
                1 => ['success_rate' => 0.40, 'avg_duration' => 25] // Wsparcie techniczne - SŁABY
            ],
            'tech_expert' => [
                1 => ['success_rate' => 0.92, 'avg_duration' => 18], // Wsparcie techniczne - ŚWIETNY  
                3 => ['success_rate' => 0.85, 'avg_duration' => 35]  // Reklamacje - DOBRY
            ],
            'universal' => [
                0 => ['success_rate' => 0.75, 'avg_duration' => 12], // Obsługa klienta - OK
                1 => ['success_rate' => 0.70, 'avg_duration' => 22], // Wsparcie techniczne - OK
                2 => ['success_rate' => 0.65, 'avg_duration' => 15], // Sprzedaż - OK
                3 => ['success_rate' => 0.72, 'avg_duration' => 28]  // Reklamacje - OK
            ],
            'sales_expert' => [
                2 => ['success_rate' => 0.88, 'avg_duration' => 12], // Sprzedaż - ŚWIETNY
                0 => ['success_rate' => 0.78, 'avg_duration' => 10]  // Obsługa klienta - DOBRY
            ],
            'junior' => [
                0 => ['success_rate' => 0.35, 'avg_duration' => 18], // Obsługa klienta - SŁABY
                2 => ['success_rate' => 0.25, 'avg_duration' => 20]  // Sprzedaż - SŁABY
            ]
        ];

        foreach ($agentEntities as $agentData) {
            $agent = $agentData['entity'];
            $profile = $agentData['profile'];
            
            if (!isset($profiles[$profile])) continue;
            
            foreach ($profiles[$profile] as $queueIndex => $stats) {
                $queue = $queueEntities[$queueIndex];
                
                // Generuj 50-100 logów dla każdego agenta w każdej kolejce
                $logCount = rand(50, 100);
                
                for ($i = 0; $i < $logCount; $i++) {
                    $log = new AgentActivityLog();
                    $log->setAgent($agent);
                    $log->setQueue($queue);
                    
                    // Losowa data z ostatnich 60 dni
                    $randomDate = $this->randomDateBetween($startDate, $endDate);
                    $startTime = clone $randomDate;
                    
                    // Czas trwania na podstawie profilu (z małą losowością)
                    $baseDuration = $stats['avg_duration'];
                    $duration = $baseDuration + rand(-5, 10); // +/- 5-10 minut
                    $duration = max(2, $duration); // Minimum 2 minuty
                    
                    $endTime = clone $startTime;
                    $endTime->modify("+{$duration} minutes");
                    
                    $log->setActivityStartDatetime($startTime);
                    $log->setActivityEndDatetime($endTime);
                    
                    // Sukces na podstawie profilu
                    $successRate = $stats['success_rate'];
                    $wasSuccessful = (rand(1, 100) <= ($successRate * 100));
                    $log->setWasSuccessful($wasSuccessful);
                    
                    $log->setActivityReferenceId('CALL_' . uniqid());
                    
                    $manager->persist($log);
                }
            }
        }
    }

    private function createAvailabilityExceptions(ObjectManager $manager, array $agentEntities): void
    {
        // Dodaj kilka urlopów/wyjątków
        $exceptions = [
            ['agent_index' => 0, 'start' => '+5 days 08:00', 'end' => '+7 days 16:00'], // Jan urlop
            ['agent_index' => 1, 'start' => '+10 days 14:00', 'end' => '+10 days 17:00'], // Anna u lekarza
            ['agent_index' => 4, 'start' => '+3 days 08:00', 'end' => '+3 days 12:00']  // Maria szkolenie
        ];

        foreach ($exceptions as $exceptionData) {
            $exception = new AgentAvailabilityException();
            $exception->setAgent($agentEntities[$exceptionData['agent_index']]['entity']);
            $exception->setUnavailableDatetimeStart(new \DateTime($exceptionData['start']));
            $exception->setUnavailableDatetimeEnd(new \DateTime($exceptionData['end']));
            $manager->persist($exception);
        }
    }

    private function createScheduleEntries(ObjectManager $manager, array $agentEntities, array $queueEntities): void
    {
        // Dodaj kilka przykładowych wpisów w grafiku na przyszłość
        $schedules = [
            ['agent' => 0, 'queue' => 0, 'date' => '+1 day', 'start' => '08:00', 'end' => '12:00'],
            ['agent' => 1, 'queue' => 1, 'date' => '+1 day', 'start' => '09:00', 'end' => '13:00'],
            ['agent' => 2, 'queue' => 2, 'date' => '+2 day', 'start' => '07:00', 'end' => '11:00']
        ];

        foreach ($schedules as $scheduleData) {
            $schedule = new Schedule();
            $schedule->setAgent($agentEntities[$scheduleData['agent']]['entity']);
            $schedule->setQueue($queueEntities[$scheduleData['queue']]);
            $schedule->setScheduleDate(new \DateTime($scheduleData['date']));
            $schedule->setTimeSlotStart(new \DateTime($scheduleData['start']));
            $schedule->setTimeSlotEnd(new \DateTime($scheduleData['end']));
            $schedule->setEntryStatus('scheduled');
            $manager->persist($schedule);
        }
    }

    private function createQueueLoadTrends(ObjectManager $manager, array $queueEntities): void
    {
        $quarters = [1, 2, 3, 4];
        $years = [date('Y') - 1, date('Y')];
        $metricNames = ['average_call_time', 'success_rate_percentage', 'calls_per_hour'];
        
        foreach ($queueEntities as $queue) {
            foreach ($years as $year) {
                foreach ($quarters as $quarter) {
                    foreach ($metricNames as $metricName) {
                        $metricValue = '';
                        $additionalDescription = '';
                        
                        switch ($metricName) {
                            case 'average_call_time':
                                $metricValue = rand(120, 600); // 2-10 minut w sekundach
                                $additionalDescription = 'Średni czas rozmowy w sekundach';
                                break;
                            
                            case 'success_rate_percentage':
                                $metricValue = rand(65, 95);
                                $additionalDescription = 'Procent połączeń zakończonych sukcesem';
                                break;
                                
                            case 'calls_per_hour':
                                $metricValue = rand(5, 20);
                                $additionalDescription = 'Średnia liczba połączeń na godzinę';
                                break;
                        }
                        
                        $calculationDate = new \DateTime();
                        $calculationDate->setDate($year, $quarter * 3, 1);
                        
                        $trend = new QueueLoadTrend();
                        $trend->setQueue($queue);
                        $trend->setYear($year);
                        $trend->setQuarter($quarter);
                        $trend->setCalculationDate($calculationDate);
                        $trend->setMetricName($metricName);
                        $trend->setMetricValue((string) $metricValue);
                        $trend->setAdditionalDescription($additionalDescription);
                        
                        $manager->persist($trend);
                    }
                }
            }
        }
    }

    private function randomDateBetween(\DateTime $start, \DateTime $end): \DateTime
    {
        $diff = $end->getTimestamp() - $start->getTimestamp();
        $randomSeconds = rand(0, $diff);
        return (clone $start)->modify("+{$randomSeconds} seconds");
    }
}
