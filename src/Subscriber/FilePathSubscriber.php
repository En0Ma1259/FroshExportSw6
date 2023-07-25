<?php declare(strict_types=1);

namespace Frosh\Exporter\Subscriber;

use Frosh\Exporter\Entity\FroshExportEntity;
use Frosh\Exporter\Export\Exporter;
use Frosh\Exporter\Export\Formatter\AbstractFormatter;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilePathSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'frosh_export.loaded' => 'addFilePath',
        ];
    }

    public function addFilePath(EntityLoadedEvent $event): void
    {
        /** @var FroshExportEntity $entity */
        foreach ($event->getEntities() as $entity) {
            $formatter = $this->container->get(Exporter::FORMATTER_TAG . $entity->getFormatter());
            if (!$formatter instanceof AbstractFormatter) {
                continue;
            }

            $entity->setFilePath($formatter::buildFileName($entity));
        }
    }
}
