<?php declare(strict_types=1);

namespace Frosh\Exporter\Message;

class FroshExportMessage
{
    public function __construct(
        protected string $froshExport
    ) {
    }

    public function getFroshExport(): string
    {
        return $this->froshExport;
    }
}
