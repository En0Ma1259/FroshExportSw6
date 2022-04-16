<?php declare(strict_types=1);

namespace Frosh\Exporter\Export;

use Doctrine\DBAL\Connection;
use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Export\Formatter\AbstractFormatter;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Exporter
{
    public const FORMATTER_TAG = 'frosh.export.formatter.';

    public function __construct(
        protected EntityRepositoryInterface $entityRepository,
        protected Reader                    $reader,
        protected ContainerInterface        $container
    ) {
    }

    public function export(string $froshExportId): string
    {
        /** @var FroshExportEntity $froshExport */
        $froshExport = $this->entityRepository->search(new Criteria([$froshExportId]), Context::createDefaultContext())->first();
        if ($froshExport === null) {
            throw new \InvalidArgumentException('Entity not found: ' . $froshExportId);
        }

        $formatter = $this->container->get(self::FORMATTER_TAG . $froshExport->getFormatter());
        if (!$formatter instanceof AbstractFormatter) {
            throw new \InvalidArgumentException('Formatter type invalid');
        }

        $fileName = $froshExport->getId() . '/' . preg_replace('/[^A-Za-z0-9\-]/', '', $froshExport->getName());
        $formatter->setFilename($fileName);

        try {
            $formatter->startFile($froshExport);
            $this->reader->readEntities($froshExport, $formatter);
        } finally {
            $formatter->endFile($froshExport);

            /** @var Connection $connection */
            $connection = $this->container->get(Connection::class);
            $connection->executeStatement('UPDATE `frosh_export` SET `latest_execute` = :now WHERE `id` = UNHEX(:id)', [
                'id'  => $froshExportId,
                'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        return 'frosh-export/' . $formatter->getFilename(false);
    }
}
