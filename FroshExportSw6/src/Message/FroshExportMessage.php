<?php declare(strict_types=1);

namespace Frosh\Exporter\Message;

class FroshExportMessage
{
    protected string $froshExport;

    public function __construct(string $froshExport)
    {
        $this->froshExport = $froshExport;
    }

    public function getFroshExport(): string
    {
        return $this->froshExport;
    }
}
