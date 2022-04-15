<?php declare(strict_types=1);

namespace Frosh\Exporter\Message;

use Frosh\Exporter\Export\Exporter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FroshExportHandler implements MessageHandlerInterface
{
    protected Exporter $exporter;

    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    public function __invoke(FroshExportMessage $exportMessage): void
    {
        $this->exporter->export($exportMessage->getFroshExport());
    }
}
