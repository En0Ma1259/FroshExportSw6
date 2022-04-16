<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Struct\ExportItem;
use League\Flysystem\Filesystem;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

abstract class AbstractFormatter
{
    public string $fileName;

    public function __construct(
        protected Filesystem $filesystem
    ) {
        $this->filesystem->getConfig()->set('disable_asserts', true);
    }

    abstract public static function fileExtension(): string;

    abstract protected function writeItem(ExportItem $item): void;

    public function setFilename(string $fileName): void
    {
        $this->fileName = $fileName . '.' . static::fileExtension();
    }

    public function startFile(FroshExportEntity $exportEntity): void
    {
        $this->filesystem->delete($this->getFilename());
    }

    public function endFile(FroshExportEntity $exportEntity): void
    {
        $this->filesystem->delete($this->getFilename(false));
        $this->filesystem->rename($this->getFilename(), $this->getFilename(false));
    }

    public function getFilename(bool $temp = true): string
    {
        return $this->fileName . ($temp ? '.temp' : '');
    }

    public function enrichData(FroshExportEntity $exportEntity, EntitySearchResult $searchResult): void
    {
        foreach ($searchResult as $item) {
            $exportItem = new ExportItem();
            foreach ($exportEntity->getFields() as $attribute) {
                $this->addFieldValue($item, explode('.', $attribute), $exportItem);
            }

            $this->writeItem($exportItem);
        }
    }

    protected function addFieldValue(Entity $entity, array $fields, ExportItem $exportItem): void
    {
        $property = array_shift($fields);
        if (!$entity->has($property) && !$entity->hasExtension($property)) {
            $exportItem->set($property, null);

            return;
        }

        $value = $entity->get($property);
        if ($value instanceof Entity && !empty($fields)) {
            $this->addFieldValue($value, $fields, $exportItem->getItem($property));

            return;
        }

        if ($value instanceof EntityCollection && !empty($fields)) {
            $this->addCollectionValues($value, $fields, $exportItem, $property);

            return;
        }

        $exportItem->set($property, $value);
    }

    protected function addCollectionValues(EntityCollection $collection, array $fields, ExportItem $exportItem, string $property): void
    {
        foreach ($collection as $item) {
            $this->addFieldValue($item, $fields, $exportItem->getCollectionItem($property, $item->getUniqueIdentifier()));
        }
    }

    protected function formatAttributes(array $fields): array
    {
        $attributes = [];
        foreach ($fields as $field) {
            $key = implode('', array_map('ucfirst', explode('.', $field)));

            $attributes[lcfirst($key)] = $field;
        }

        return $attributes;
    }
}
