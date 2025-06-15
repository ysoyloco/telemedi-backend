<?php

namespace App\DataFixtures;

use App\Entity\Agent;
use App\Entity\Queue;
use App\Entity\Schedule;
use App\Entity\AgentActivityLog;
use App\Entity\AgentAvailabilityException;
use App\Entity\QueueLoadTrend;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Utwórz kilka kolejek
        $queues = [];
        $queueNames = ['Obsługa klienta', 'Wsparcie techniczne', 'Sprzedaż', 'Reklamacje'];
        
        foreach ($queueNames as $index => $name) {
            $queue = new Queue();
            $queue->setQueueName($name);
            $queue->setPriority($index + 1);
            $queue->setDescription('Opis kolejki ' . $name);
            $queue->setTargetHandledCallsPerSlot(10);
            $queue->setTargetSuccessRatePercentage(85);
            
            $manager->persist($queue);
            $queues[] = $queue;
        }
        
        // Utwórz kilku agentów
        $agents = [];
        $agentNames = ['Jan Kowalski', 'Anna Nowak', 'Piotr Wiśniewski', 'Katarzyna Dąbrowska'];
        
        foreach ($agentNames as $index => $name) {
            $agent = new Agent();
            $agent->setFullName($name);
            $agent->setEmail(strtolower(str_replace(' ', '.', $name)) . '@example.com');
            $agent->setIsActive(true);
            
            // Ustaw domyślny wzorzec dostępności
            $agent->setDefaultAvailabilityPattern([
                'Mon' => ['08:00-16:00'],
                'Tue' => ['08:00-16:00'],
                'Wed' => ['08:00-16:00'],
                'Thu' => ['08:00-16:00'],
                'Fri' => ['08:00-16:00']
            ]);
            
            // Przypisz agentowi umiejętności (obsługiwane kolejki)
            foreach ($queues as $queueIndex => $queue) {
                if ($index === $queueIndex || rand(0, 1)) {
                    $agent->addQueue($queue);
                }
            }
            
            $manager->persist($agent);
            $agents[] = $agent;
        }
        
        // Utwórz kilka wpisów w grafiku
        $statuses = ['scheduled', 'completed', 'cancelled'];
        $today = new \DateTime();
        $schedules = [];
        
        for ($i = 0; $i < 20; $i++) {
            $scheduleDate = clone $today;
            $scheduleDate->modify('+' . rand(0, 10) . ' days');
            
            $timeSlotStart = new \DateTime('08:00:00');
            $timeSlotStart->modify('+' . rand(0, 8) . ' hours');
            
            $timeSlotEnd = clone $timeSlotStart;
            $timeSlotEnd->modify('+1 hour');
            
            $agent = $agents[array_rand($agents)];
            $queue = $agent->getQueues()->toArray()[array_rand($agent->getQueues()->toArray())];
            
            $schedule = new Schedule();
            $schedule->setAgent($agent);
            $schedule->setQueue($queue);
            $schedule->setScheduleDate($scheduleDate);
            $schedule->setTimeSlotStart($timeSlotStart);
            $schedule->setTimeSlotEnd($timeSlotEnd);
            $schedule->setEntryStatus($statuses[array_rand($statuses)]);
            
            $manager->persist($schedule);
            $schedules[] = $schedule;
        }
        
        // Utwórz kilka logów aktywności agentów
        for ($i = 0; $i < 30; $i++) {
            $agent = $agents[array_rand($agents)];
            $queue = $agent->getQueues()->toArray()[array_rand($agent->getQueues()->toArray())];
            
            $activityStart = clone $today;
            $activityStart->modify('-' . rand(1, 30) . ' days');
            $activityStart->modify('+' . rand(8, 16) . ' hours');
            
            $activityEnd = clone $activityStart;
            $activityEnd->modify('+' . rand(5, 30) . ' minutes');
            
            $wasSuccessful = (rand(0, 100) > 30); // 70% szans na sukces
            
            $activityLog = new AgentActivityLog();
            $activityLog->setAgent($agent);
            $activityLog->setQueue($queue);
            $activityLog->setActivityStartDatetime($activityStart);
            $activityLog->setActivityEndDatetime($activityEnd);
            $activityLog->setWasSuccessful($wasSuccessful);
            $activityLog->setActivityReferenceId('CALL-' . rand(10000, 99999));
            
            $manager->persist($activityLog);
        }
        
        // Utwórz kilka wyjątków dostępności agentów (urlopy, zwolnienia, itp.)
        for ($i = 0; $i < 10; $i++) {
            $agent = $agents[array_rand($agents)];
            
            $startDate = clone $today;
            $startDate->modify('+' . rand(1, 30) . ' days');
            $startDate->setTime(0, 0, 0);
            
            $endDate = clone $startDate;
            $endDate->modify('+' . rand(1, 7) . ' days');
            
            $exception = new AgentAvailabilityException();
            $exception->setAgent($agent);
            $exception->setUnavailableDatetimeStart($startDate);
            $exception->setUnavailableDatetimeEnd($endDate);
            
            $manager->persist($exception);
        }
        
        // Utwórz trendy obciążenia kolejek
        $quarters = [1, 2, 3, 4];
        $years = [date('Y') - 1, date('Y')];
        $metricNames = ['average_call_time', 'success_rate_percentage', 'calls_per_hour'];
        
        foreach ($queues as $queue) {
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
        
        $manager->flush();
    }
} 
 