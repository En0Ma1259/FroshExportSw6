<?php declare(strict_types=1);

namespace Frosh\ViewExporter\Export\Formatter;

use Frosh\ViewExporter\Entity\FroshExportEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class CSV extends AbstractFormatter
{
    public function enrichData(FroshExportEntity $exportEntity, EntitySearchResult $searchResult, bool $direct): void
    {
        if (empty($this->data)) {
            if ($direct) {
                $this->writeItem(array_keys($this->formatAttributes($exportEntity->getFields())));
            } else {
                $this->data[] = $this->formatAttributes($exportEntity->getFields());
            }
        }

        parent::enrichData($exportEntity, $searchResult, $direct);
    }

    protected function getCollectionValues(EntityCollection $collection, array $fields)
    {
        $value = null;
        if ($collection->first() !== null) {
            $value = $this->getFieldValue($collection->first(), $fields);
        }

        return $value;
    }

    public static function fileExtension(): string
    {
        return 'csv';
    }

    public function writeItem($item): void
    {
        $this->filesystem->put($this->fileName, implode(';', $item) . "\n");
    }

    public function __toString(): string
    {
        return implode("\n", $this->data);
    }
}
