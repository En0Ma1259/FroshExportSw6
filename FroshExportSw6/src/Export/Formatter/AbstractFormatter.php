<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use League\Flysystem\Filesystem;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

abstract class AbstractFormatter
{
    public string $fileName;

    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->filesystem->getConfig()->set('disable_asserts', true);
    }

    abstract public static function fileExtension(): string;

    abstract public function writeItem($item): void;

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
            $values = [];
            foreach ($this->formatAttributes($exportEntity->getFields()) as $key => $attribute) {
                $values[$key] = $this->getFieldValue($item, explode('.', $attribute));
            }

            $this->writeItem($values);
        }
    }

    protected function getFieldValue(Entity $entity, array $fields)
    {
        $property = array_shift($fields);
        if (!$entity->has($property)) {
            return null;
        }

        $value = $entity->get($property);
        if ($value instanceof Entity && !empty($fields)) {
            $value = $this->getFieldValue($value, $fields);
        }

        if ($value instanceof EntityCollection && !empty($fields)) {
            $value = $this->getCollectionValues($value, $fields);
        }

        return $value;
    }

    protected function getCollectionValues(EntityCollection $collection, array $fields): array
    {
        $data = [];
        foreach ($collection as $item) {
            $data[$item->getUniqueIdentifier()] = $this->getFieldValue($item, $fields);
        }

        return $data;
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
