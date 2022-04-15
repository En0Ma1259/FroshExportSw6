<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class CSV extends AbstractFormatter
{
    public function startFile(FroshExportEntity $exportEntity): void
    {
        parent::startFile($exportEntity);

        $this->writeItem(array_keys($this->formatAttributes($exportEntity->getFields())));
    }

    protected function getFieldValue(Entity $entity, array $fields)
    {
        $value = parent::getFieldValue($entity, $fields);

        if (is_array($value)) {
            $value = array_shift($value);
        }

        if (is_string($value) && strpos($value, '"') !== 0) {
            $value = '"' . $value . '"';
        }

        return $value;
    }

    public static function fileExtension(): string
    {
        return 'csv';
    }

    public function writeItem($item): void
    {
        $this->filesystem->put($this->getFilename(), implode(';', $item) . "\n");
    }
}
