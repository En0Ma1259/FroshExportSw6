<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Export\Formatter;

use Frosh\ViewExporter\Entity\FroshExportEntity;
use League\Flysystem\Filesystem;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

abstract class AbstractFormatter
{
    public string $fileName;

    protected array $data = [];

    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->filesystem->getConfig()->set('disable_asserts', true);
    }

    abstract public static function fileExtension(): string;

    abstract public function writeItem($item): void;

    abstract public function __toString(): string;

    public function setFilename(string $fileName): void
    {
        $this->fileName = $fileName . '.' . static::fileExtension();
    }

    public function startFile(): void
    {
        $this->filesystem->delete($this->fileName);
    }

    public function writeFile(): void
    {
        $this->filesystem->put($this->fileName, $this->__toString());
    }

    public function endFile(): void
    {
    }

    public function getFilename(): string
    {
        return $this->fileName;
    }

    public function enrichData(FroshExportEntity $exportEntity, EntitySearchResult $searchResult, bool $direct): void
    {
        foreach ($searchResult as $item) {
            $values = [];
            foreach ($this->formatAttributes($exportEntity->getFields()) as $key => $attribute) {
                $values[$key] = $this->getFieldValue($item, explode('.', $attribute));
            }

            if ($direct) {
                $this->writeItem($values);
            } else {
                $this->data[] = $values;
            }
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

    protected function getCollectionValues(EntityCollection $collection, array $fields)
    {
        $data = [];
        foreach ($collection as $item) {
            $data[] = $this->getFieldValue($item, $fields);
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
