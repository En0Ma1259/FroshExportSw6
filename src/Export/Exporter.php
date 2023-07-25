<?php declare(strict_types=1);

namespace Frosh\Exporter\Export;

use Doctrine\DBAL\Connection;
use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Export\Formatter\AbstractFormatter;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Exporter
{
    public const FORMATTER_TAG = 'frosh.export.formatter.';

    public function __construct(
        protected EntityRepository   $entityRepository,
        protected Reader             $reader,
        protected ContainerInterface $container
    ) {
    }

    public function export(string $froshExportId, Context $context, ?Criteria $additionalCriteria = null): string
    {
        /** @var FroshExportEntity $froshExport */
        $froshExport = $this->entityRepository->search(new Criteria([$froshExportId]), $context)->first();
        if ($froshExport === null) {
            throw new \InvalidArgumentException('Entity not found: ' . $froshExportId);
        }

        $formatter = $this->container->get(self::FORMATTER_TAG . $froshExport->getFormatter());
        if (!$formatter instanceof AbstractFormatter) {
            throw new \InvalidArgumentException('Formatter type invalid');
        }

        try {
            $formatter->setExportEntity($froshExport);
            $formatter->startFile($froshExport);

            $this->reader->readEntities($froshExport, $formatter, $context, $additionalCriteria);

            $formatter->endFile($froshExport);
        } finally {
            /** @var Connection $connection */
            $connection = $this->container->get(Connection::class);
            $connection->executeStatement('UPDATE `frosh_export` SET `latest_execute` = :now WHERE `id` = UNHEX(:id)', [
                'id'  => $froshExportId,
                'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        return $formatter->getFilename();
    }
}
