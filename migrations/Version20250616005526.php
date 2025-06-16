<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616005526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE agent (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, default_availability_pattern JSON NOT NULL COMMENT '(DC2Type:json)', is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_268B9C9DE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE agent_queue (agent_id INT NOT NULL, queue_id INT NOT NULL, INDEX IDX_4653571B3414710B (agent_id), INDEX IDX_4653571B477B5BAE (queue_id), PRIMARY KEY(agent_id, queue_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE agent_activity_log (id INT AUTO_INCREMENT NOT NULL, agent_id INT NOT NULL, queue_id INT NOT NULL, activity_start_datetime DATETIME NOT NULL, activity_end_datetime DATETIME NOT NULL, was_successful TINYINT(1) NOT NULL, activity_reference_id VARCHAR(255) DEFAULT NULL, INDEX IDX_8B4A798C3414710B (agent_id), INDEX IDX_8B4A798C477B5BAE (queue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE agent_availability_exceptions (id INT AUTO_INCREMENT NOT NULL, agent_id INT NOT NULL, unavailable_datetime_start DATETIME DEFAULT NULL, unavailable_datetime_end DATETIME DEFAULT NULL, INDEX IDX_9D10C6883414710B (agent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE queue_load_trends (id INT AUTO_INCREMENT NOT NULL, queue_id INT NOT NULL, year INT NOT NULL, quarter INT NOT NULL, calculation_date DATE NOT NULL, metric_name VARCHAR(255) NOT NULL, metric_value VARCHAR(255) NOT NULL, additional_description LONGTEXT DEFAULT NULL, INDEX IDX_E80F7A4E477B5BAE (queue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE queues (id INT AUTO_INCREMENT NOT NULL, queue_name VARCHAR(255) NOT NULL, priority INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, target_handled_calls_per_slot INT DEFAULT NULL, target_success_rate_percentage NUMERIC(5, 2) DEFAULT NULL, UNIQUE INDEX UNIQ_CFCA0296FB7336F0 (queue_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE schedules (id INT AUTO_INCREMENT NOT NULL, agent_id INT NOT NULL, queue_id INT NOT NULL, schedule_date DATE NOT NULL, time_slot_start TIME NOT NULL, time_slot_end TIME NOT NULL, entry_status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_313BDC8E3414710B (agent_id), INDEX IDX_313BDC8E477B5BAE (queue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_queue ADD CONSTRAINT FK_4653571B3414710B FOREIGN KEY (agent_id) REFERENCES agent (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_queue ADD CONSTRAINT FK_4653571B477B5BAE FOREIGN KEY (queue_id) REFERENCES queues (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_activity_log ADD CONSTRAINT FK_8B4A798C3414710B FOREIGN KEY (agent_id) REFERENCES agent (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_activity_log ADD CONSTRAINT FK_8B4A798C477B5BAE FOREIGN KEY (queue_id) REFERENCES queues (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_availability_exceptions ADD CONSTRAINT FK_9D10C6883414710B FOREIGN KEY (agent_id) REFERENCES agent (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE queue_load_trends ADD CONSTRAINT FK_E80F7A4E477B5BAE FOREIGN KEY (queue_id) REFERENCES queues (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8E3414710B FOREIGN KEY (agent_id) REFERENCES agent (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedules ADD CONSTRAINT FK_313BDC8E477B5BAE FOREIGN KEY (queue_id) REFERENCES queues (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_queue DROP FOREIGN KEY FK_4653571B3414710B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_queue DROP FOREIGN KEY FK_4653571B477B5BAE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_activity_log DROP FOREIGN KEY FK_8B4A798C3414710B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_activity_log DROP FOREIGN KEY FK_8B4A798C477B5BAE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agent_availability_exceptions DROP FOREIGN KEY FK_9D10C6883414710B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE queue_load_trends DROP FOREIGN KEY FK_E80F7A4E477B5BAE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8E3414710B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE schedules DROP FOREIGN KEY FK_313BDC8E477B5BAE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE agent
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE agent_queue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE agent_activity_log
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE agent_availability_exceptions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE queue_load_trends
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE queues
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE schedules
        SQL);
    }
}
