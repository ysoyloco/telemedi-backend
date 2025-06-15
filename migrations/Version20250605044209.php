<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250605044209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE queues ADD target_handled_calls_per_slot INT DEFAULT NULL COMMENT 'Docelowa liczba obsłużonych połączeń na slot czasowy dla tej kolejki', ADD target_success_rate_percentage NUMERIC(5, 2) DEFAULT NULL COMMENT 'Docelowy procent połączeń zakończonych sukcesem (np. 90.50 dla 90.5%)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE queues DROP target_handled_calls_per_slot, DROP target_success_rate_percentage
        SQL);
    }
}
