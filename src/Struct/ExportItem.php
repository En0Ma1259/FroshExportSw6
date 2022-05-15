<?php declare(strict_types=1);

namespace Frosh\Exporter\Struct;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;

class ExportItem
{
    use JsonSerializableTrait;

    public function set(string $property, $value): void
    {
        if ($value instanceof Entity) {
            $value = $this->convertEntity($value);
        } elseif ($value instanceof EntityCollection) {
            $value = $this->convertCollection($value);
        }

        $this->$property = $value;
    }

    public function get(string $property): mixed
    {
        return $this->$property ?? null;
    }

    public function addValues(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getItem(string $property): self
    {
        if (!isset($this->$property)) {
            $this->$property = new self();
        }

        return $this->$property;
    }

    public function getCollectionItem(string $property, string $id): self
    {
        if (!isset($this->$property)) {
            $this->$property = new ExportItemCollection();
        }

        $collection = $this->$property;
        if (!$collection->has($id)) {
            $collection->set($id, new self());
        }

        return $collection->get($id);
    }

    protected function convertEntity(Entity $entity): self
    {
        $options     = $entity->jsonSerialize();
        $froshEntity = new self();
        $froshEntity->addValues($options);

        return $froshEntity;
    }

    protected function convertCollection(EntityCollection $collection): ExportItemCollection
    {
        $values = [];
        foreach ($collection as $entity) {
            $values[] = $this->convertEntity($entity);
        }

        return new ExportItemCollection($values);
    }
}
