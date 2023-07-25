<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Struct\ExportItem;

class JsonNoFile extends AbstractFormatter
{
    protected array $items = [];

    public static function fileExtension(): string
    {
        return '';
    }

    public function startFile(FroshExportEntity $exportEntity): void
    {
    }

    public function endFile(FroshExportEntity $exportEntity): void
    {
    }

    protected function writeItem(ExportItem $item, bool $lastItem = false): void
    {
        $this->items[] = $item;
    }

    public function getResult(): string
    {
        return json_encode($this->items, JSON_THROW_ON_ERROR);
    }
}
