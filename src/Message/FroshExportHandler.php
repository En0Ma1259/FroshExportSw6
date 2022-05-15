<?php declare(strict_types=1);

namespace Frosh\Exporter\Message;

use Frosh\Exporter\Export\Exporter;
use Shopware\Core\Framework\Context;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class FroshExportHandler implements MessageHandlerInterface
{
    public function __construct(
        protected Exporter $exporter
    ) {
    }

    public function __invoke(FroshExportMessage $exportMessage): void
    {
        $this->exporter->export($exportMessage->getFroshExport(), Context::createDefaultContext());
    }
}
