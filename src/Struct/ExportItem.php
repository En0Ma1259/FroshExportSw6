<?php declare(strict_types=1);

namespace Frosh\Exporter\Struct;

use Shopware\Core\Framework\Struct\JsonSerializableTrait;

class ExportItem
{
    use JsonSerializableTrait;

    public function set(string $property, $value): void
    {
        $this->$property = $value;
    }

    public function get(string $property): mixed
    {
        return $this->$property ?? null;
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
}
