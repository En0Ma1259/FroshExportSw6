<?php declare(strict_types=1);

namespace Frosh\Exporter\Event;

use Frosh\Exporter\Struct\ExportItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\Event\NestedEvent;

class SpecialPropertyEvent extends NestedEvent
{

    public function __construct(
        protected Entity     $currentEntity,
        protected ExportItem $currentItem,
        protected string     $property
    ) {
    }

    public function getEntity(): Entity
    {
        return $this->currentEntity;
    }

    public function getCurrentItem(): ExportItem
    {
        return $this->currentItem;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getContext(): Context
    {
        return Context::createDefaultContext();
    }
}
