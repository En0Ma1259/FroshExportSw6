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

        $this->filesystem->put($this->getFilename(), '[');
    }

    public function endFile(FroshExportEntity $exportEntity): void
    {
        // TODO: Remove last ',' and empty element '{}'
        $this->filesystem->put($this->getFilename(), '{}]');

        parent::endFile($exportEntity);
    }

    protected function writeItem(ExportItem $item): void
    {
        $this->filesystem->put($this->getFilename(), json_encode($item, JSON_THROW_ON_ERROR) . ',');
    }
}
