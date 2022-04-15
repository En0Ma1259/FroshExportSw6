<?php declare(strict_types=1);

namespace Frosh\Exporter\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(FroshExportEntity $entity)
 * @method void                   set(string $key, FroshExportEntity $entity)
 * @method FroshExportEntity[]    getIterator()
 * @method FroshExportEntity[]    getElements()
 * @method FroshExportEntity|null get(string $key)
 * @method FroshExportEntity|null first()
 * @method FroshExportEntity|null last()
 */
class FroshExportCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return FroshExportEntity::class;
    }
}
