<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Event\SpecialPropertyEvent;
use Frosh\Exporter\Struct\ExportItem;
use League\Flysystem\Filesystem;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractFormatter
{
    protected FroshExportEntity $exportEntity;

    protected array $fields = [];

    public function __construct(
        protected Filesystem               $filesystemPublic,
        protected Filesystem               $filesystemPrivate,
        protected EventDispatcherInterface $eventDispatcher
    ) {
    }

    abstract public static function fileExtension(): string;

    abstract protected function writeItem(ExportItem $item, bool $lastItem = false): void;

    public function setExportEntity(FroshExportEntity $exportEntity): void
    {
        $this->exportEntity = $exportEntity;
    }

    public static function buildFileName(FroshExportEntity $exportEntity): string
    {
        return sprintf('%s/%s/%s/%s.%s',
            'frosh-export',
            $exportEntity->getId(),
            $exportEntity->getLanguageId() ?? Defaults::LANGUAGE_SYSTEM,
            strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $exportEntity->getName())),
            static::fileExtension()
        );
    }

    public function startFile(FroshExportEntity $exportEntity): void
    {
        $this->fields = $this->formatAttributes($exportEntity->getFields());
        $this->getFileSystem()->delete($this->getFilename(true));
    }

    public function endFile(FroshExportEntity $exportEntity): void
    {
        $this->getFileSystem()->move($this->getFilename(true), $this->getFilename());
    }

    public function getFilename(bool $temp = false): string
    {
        return $this->exportEntity->getFilePath() . ($temp ? '.temp' : '');
    }

    public function enrichData(FroshExportEntity $exportEntity, EntitySearchResult $searchResult, bool $lastIteration): void
    {
        $lastElementId = $searchResult->last()->getId();

        foreach ($searchResult as $item) {
            $exportItem = new ExportItem();
            foreach ($exportEntity->getFields() as $attribute) {
                $this->addFieldValue($item, array_filter(explode('.', $attribute)), $exportItem);
            }

            $this->writeItem($exportItem, $lastIteration && $lastElementId === $item->getId());
        }
    }

    protected function getFileSystem(): Filesystem
    {
        return ($this->exportEntity->isPrivate()) ? $this->filesystemPrivate : $this->filesystemPublic;
    }

    protected function addFieldValue(Entity $entity, array $fields, ExportItem $exportItem): void
    {
        $property = array_shift($fields);
        if (!$entity->has($property) && !$entity->hasExtension($property)) {
            $event = new SpecialPropertyEvent($entity, $exportItem, $property);
            $this->eventDispatcher->dispatch($event);

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
