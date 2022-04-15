<?php declare(strict_types=1);

namespace Frosh\Exporter\Export;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Export\Formatter\AbstractFormatter;
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
        $language = [Defaults::LANGUAGE_SYSTEM];
        if ($exportEntity->getLanguageId() !== null) {
            $language = [$exportEntity->getLanguageId()];
        }

        $context  = new Context(new SystemSource(), [], Defaults::CURRENCY, $language);
        $criteria = $exportEntity->getRealCriteria();
        $this->addAssociations($criteria, $exportEntity->getFields());

        if ($exportEntity->getProductStreamId() !== null) {
            $this->addProductStreamFilter($criteria, $exportEntity->getProductStreamId(), $context);
        }

        $entityRepository   = $this->definitionRegistry->getRepository($exportEntity->getEntity());
        $repositoryIterator = new RepositoryIterator($entityRepository, $context, $criteria);
        while (($result = $repositoryIterator->fetch()) !== null) {
            $formatter->enrichData($exportEntity, $result);
            if ($result->count() < $criteria->getLimit()) {
                break;
            }
        }
    }

    protected function addProductStreamFilter(Criteria $criteria, string $productStreamId, Context $context): void
    {
        $criteria->addFilter(...$this->streamBuilder->buildFilters($productStreamId, $context));
    }

    protected function addAssociations(Criteria $criteria, array $fields): void
    {
        foreach ($fields as $field) {
            $offset = strrpos($field, '.');
            if ($offset === false) {
                continue;
            }

            $association = substr($field, 0, $offset);
            $criteria->addAssociation($association);
        }
    }
}
