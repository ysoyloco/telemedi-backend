<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609015743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON agent_skills
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_skills ADD PRIMARY KEY (agent_id, queue_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE queues CHANGE target_handled_calls_per_slot target_handled_calls_per_slot INT DEFAULT NULL, CHANGE target_success_rate_percentage target_success_rate_percentage NUMERIC(5, 2) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE queues CHANGE target_handled_calls_per_slot target_handled_calls_per_slot INT DEFAULT NULL COMMENT 'Docelowa liczba obsłużonych połączeń na slot czasowy dla tej kolejki', CHANGE target_success_rate_percentage target_success_rate_percentage NUMERIC(5, 2) DEFAULT NULL COMMENT 'Docelowy procent połączeń zakończonych sukcesem (np. 90.50 dla 90.5%)'
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON agent_skills
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_skills ADD PRIMARY KEY (queue_id, agent_id)
        SQL);
    }
}
