<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Core\Content\ProductStream;

use Frosh\ViewExporter\Entity\FroshExportDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductStreamExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField(
                'froshExports',
                FroshExportDefinition::class,
                'product_stream_id')
        );
    }

    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }
}
