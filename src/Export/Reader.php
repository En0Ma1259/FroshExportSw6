<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Export;

use Frosh\ViewExporter\Entity\FroshExportEntity;
use Frosh\ViewExporter\Export\Formatter\AbstractFormatter;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilder;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class Reader
{
    protected DefinitionInstanceRegistry $definitionRegistry;
    protected ProductStreamBuilder       $streamBuilder;

    public function __construct(
        DefinitionInstanceRegistry $definitionRegistry,
        ProductStreamBuilder       $streamBuilder
    ) {
        $this->definitionRegistry = $definitionRegistry;
        $this->streamBuilder      = $streamBuilder;
    }

    public function readEntities(FroshExportEntity $exportEntity, AbstractFormatter $formatter): void
    {
        $context          = new Context(new SystemSource(), [], Defaults::CURRENCY, [$exportEntity->getLanguageId()]);
        $entityRepository = $this->definitionRegistry->getRepository($exportEntity->getEntity());
        $criteria         = $exportEntity->getRealCriteria();

        if ($exportEntity->getProductStreamId() !== null) {
            $this->addProductStreamFilter($criteria, $exportEntity->getProductStreamId(), $context);
        }

        $repositoryIterator = new RepositoryIterator($entityRepository, $context, $criteria);
        while (($result = $repositoryIterator->fetch()) !== null) {
            $formatter->enrichData($exportEntity, $result, true);
            if ($result->count() < $criteria->getLimit()) {
                break;
            }
        }
    }

    protected function addProductStreamFilter(Criteria $criteria, string $productStreamId, Context $context): void
    {
        $criteria->addFilter(...$this->streamBuilder->buildFilters($productStreamId, $context));
    }
}
