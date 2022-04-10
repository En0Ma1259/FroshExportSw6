<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Export;

use Frosh\ViewExporter\Entity\FroshExportEntity;
use Frosh\ViewExporter\Export\Formatter\AbstractFormatter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Exporter
{
    public const FORMATTER_TAG = 'frosh.export.formatter.';

    protected EntityRepositoryInterface $entityRepository;
    protected Reader                    $reader;
    protected ContainerInterface        $container;

    public function __construct(
        EntityRepositoryInterface $entityRepository,
        Reader                    $reader,
        ContainerInterface        $container
    ) {
        $this->entityRepository = $entityRepository;
        $this->reader           = $reader;
        $this->container        = $container;
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
            $formatter->startFile();
            $this->reader->readEntities($froshExport, $formatter);
        } finally {
            $formatter->endFile();
        }

        return 'frosh-export/' . $formatter->getFilename();
    }
}
