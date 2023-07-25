<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Struct\ExportItem;
use Frosh\Exporter\Struct\ExportItemCollection;

class Csv extends AbstractFormatter
{
    public function startFile(FroshExportEntity $exportEntity): void
    {
        parent::startFile($exportEntity);

        $this->getFileSystem()->write($this->getFilename(true), implode(';', array_keys($this->fields)) . "\n");
    }

    public static function fileExtension(): string
    {
        return 'csv';
    }

    protected function writeItem(ExportItem $item, bool $lastItem = false): void
    {
        $values = [];
        foreach ($this->fields as $field) {
            $values[] = $this->formatValue($item, explode('.', $field));
        }

        $this->getFileSystem()->write($this->getFilename(true), implode(';', $values) . "\n");
    }

    protected function formatValue(ExportItem $item, array $fields)
    {
        $property = array_shift($fields);
        $value    = $item->get($property);

        if ($value instanceof ExportItem) {
            $value = $this->formatValue($value, $fields);
        }

        if ($value instanceof ExportItemCollection) {
            $value = $this->formatValue($value->first(), $fields);
        }

        return is_string($value) ? $this->validateString($value) : $value;
    }

    protected function validateString(string $value): string
    {
        if (str_starts_with('"', $value) && str_ends_with('"', $value)) {
            $value = trim($value, '"');
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }
}
