<?php declare(strict_types=1);

namespace Frosh\Exporter\Export;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Symfony\Component\HttpFoundation\Request;

class CriteriaBuilder
{
    public function __construct(
        protected Connection                 $connection,
        protected DefinitionInstanceRegistry $definitionRegistry,
        protected RequestCriteriaBuilder     $criteriaBuilder,
    ) {
    }

    public function buildCriteria(string $exportId, Request $request, Context $context, Criteria $criteria = null): Criteria
    {
        $criteria = $criteria ?: new Criteria();
        $this->criteriaBuilder->handleRequest(
            $request,
            $criteria,
            $this->loadEntityDefinition($exportId),
            $context
        );

        return $criteria;
    }

    public function updateCriteria(string $exportId, Criteria $criteria): void
    {
        $query = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('UPDATE `frosh_export` SET `criteria` = :criteria WHERE `id` = UNHEX(:id)')
        );

        $query->execute(['id' => $exportId, 'criteria' => serialize($criteria)]);
    }

    /**
     * @return array|Criteria|null
     */
    public function loadCriteria(string $exportId, bool $asArray = false)
    {
        $serCriteria = $this->connection->fetchOne('SELECT `criteria` FROM `frosh_export` WHERE `id` = UNHEX(:id)', ['id' => $exportId]);
        if (empty($serCriteria)) {
            return null;
        }

        $criteria = unserialize($serCriteria, [Criteria::class]);

        return $asArray ? $this->criteriaBuilder->toArray($criteria) : $criteria;
    }

    protected function loadEntityDefinition(string $exportId): EntityDefinition
    {
        $entity = $this->connection->fetchOne('SELECT `entity` FROM `frosh_export` WHERE `id` = UNHEX(:id)', ['id' => $exportId]);
        if ($entity === false) {
            throw new \Exception('Export not found');
        }

        return $this->definitionRegistry->getByEntityName($entity);
    }
}
