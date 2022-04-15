<?php declare(strict_types=1);

namespace Frosh\Exporter\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

class Migration1650010024Example extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1650010024;
    }

    public function update(Connection $connection): void
    {
        $pFields = '["name", "productNumber", "media.media.url"]';
        $cFields = '["name", "products.productNumber"]';
        $this->createExample($connection, 'json', 'product', $pFields);
        $this->createExample($connection, 'csv', 'product', $pFields);
        $this->createExample($connection, 'json', 'category', $cFields);
        $this->createExample($connection, 'csv', 'category', $cFields);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    protected function createExample(Connection $connection, string $format, string $entity, string $fields): void
    {
        $sql = <<<SQL
            INSERT INTO `frosh_export` (`id`, `name`,`formatter`, `entity`, `criteria`, `fields`, `created_at`) VALUES (:id, :name, :formatter, :entity, :criteria, :fields, :createdAt);
        SQL;
        $connection->executeStatement($sql, [
            'id'        => Uuid::randomBytes(),
            'name'      => 'Example ' . $entity . ' - ' . $format,
            'formatter' => $format,
            'entity'    => $entity,
            'criteria'  => $this->criteria(),
            'fields'    => $fields,
            'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    protected function criteria(): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        return serialize($criteria);
    }
}
