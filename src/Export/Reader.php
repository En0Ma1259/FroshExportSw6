<?php declare(strict_types=1);

namespace Frosh\Exporter\Export;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Export\Formatter\AbstractFormatter;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilder;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class Reader
{
    public function __construct(
        protected DefinitionInstanceRegistry $definitionRegistry,
        protected ProductStreamBuilder       $streamBuilder
    ) {
    }

    public function readEntities(FroshExportEntity $exportEntity, AbstractFormatter $formatter): void
    {
        $args = [
            'source'              => new SystemSource(),
            'considerInheritance' => true,
        ];
        if ($exportEntity->getLanguageId() !== null) {
            $args['languageIdChain'] = [$exportEntity->getLanguageId()];
        }

        $context            = new Context(...$args);
        $criteria           = $this->buildCriteria($exportEntity, $context);
        $entityRepository   = $this->definitionRegistry->getRepository($exportEntity->getEntity());
        $repositoryIterator = new RepositoryIterator($entityRepository, $context, $criteria);

        while (($result = $repositoryIterator->fetch()) !== null) {
            $formatter->enrichData($exportEntity, $result);
            if ($result->count() < $criteria->getLimit()) {
                break;
            }
        }
    }

    protected function buildCriteria(FroshExportEntity $exportEntity, Context $context): Criteria
    {
        $criteria = $exportEntity->getRealCriteria();
        $this->addAssociations($criteria, $exportEntity->getFields());

        if ($exportEntity->getProductStreamId() !== null) {
            $this->addProductStreamFilter($criteria, $exportEntity->getProductStreamId(), $context);
        }

        return $criteria;
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
