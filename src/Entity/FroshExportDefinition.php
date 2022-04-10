<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Entity;

use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BlobField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\User\UserDefinition;

class FroshExportDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'frosh_export';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return FroshExportCollection::class;
    }

    public function getEntityClass(): string
    {
        return FroshExportEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('entity', 'entity'))->addFlags(new Required()),
            (new StringField('formatter', 'formatter'))->addFlags(new Required()),

            new StringField('name', 'name'),
            new BlobField('criteria', 'criteria'),
            new ListField('fields', 'fields', StringField::class),

            new FkField('language_id', 'languageId', UserDefinition::class),
            new FkField('user_id', 'userId', UserDefinition::class),
            new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class),

            new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class),
            new ManyToOneAssociationField('user', 'user_id', UserDefinition::class),
            new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class),
        ]);
    }
}
