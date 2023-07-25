<?php declare(strict_types=1);

namespace Frosh\Exporter\Export\Formatter;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Struct\ExportItem;

class Json extends AbstractFormatter
{
    public static function fileExtension(): string
    {
        return 'json';
    }

    public function startFile(FroshExportEntity $exportEntity): void
    {
        parent::startFile($exportEntity);

        $this->getFileSystem()->write($this->getFilename(true), '[');
    }

    public function endFile(FroshExportEntity $exportEntity): void
    {
        $this->getFileSystem()->write($this->getFilename(true), ']');

        parent::endFile($exportEntity);
    }

    protected function writeItem(ExportItem $item, bool $lastItem = false): void
    {
        $this->getFileSystem()->write($this->getFilename(true), json_encode($item, JSON_THROW_ON_ERROR) . ($lastItem ? '' : ','));
    }
}
