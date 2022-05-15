<?php declare(strict_types=1);

namespace Frosh\Exporter\Subscriber;

use Frosh\Exporter\Event\SpecialPropertyEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Example Subscriber to add custom logic
 */
class FroshAvailableSubscriber implements EventSubscriberInterface
{
    protected const PROPERTY = 'froshAvailable';

    public static function getSubscribedEvents(): array
    {
        return [
            SpecialPropertyEvent::class => 'isAvailable',
        ];
    }

    public function isAvailable(SpecialPropertyEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof ProductEntity || $event->getProperty() !== self::PROPERTY) {
            return;
        }

        $event->getCurrentItem()->set(self::PROPERTY, (bool) $entity->getAvailableStock());
    }
}
