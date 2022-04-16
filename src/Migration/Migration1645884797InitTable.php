<?php declare(strict_types=1);

namespace Frosh\Exporter\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1645884797InitTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1645884797;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `frosh_export` (
                `id` BINARY(16) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `entity` VARCHAR(255) NOT NULL,
                `formatter` VARCHAR(255) NOT NULL,
                `criteria` LONGBLOB NULL,
                `fields` JSON NULL,
                `language_id` BINARY(16) DEFAULT NULL,
                `user_id` BINARY(16) DEFAULT NULL,
                `product_stream_id` BINARY(16) DEFAULT NULL,
                `latest_execute` DATETIME(3) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.frosh_export.language_id` (`language_id`),
                KEY `fk.frosh_export.user_id` (`user_id`),
                CONSTRAINT `fk.frosh_export.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.frosh_export.user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
