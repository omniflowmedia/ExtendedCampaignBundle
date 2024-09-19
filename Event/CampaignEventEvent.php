<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Event;

use Mautic\CampaignBundle\Entity\Event;
use Mautic\CoreBundle\Event\CommonEvent;

/**
 * Class CampaignEventEvent.
 */
class CampaignEventEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Event &$event, $isNew = false)
    {
        $this->entity = &$event;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Event entity.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->entity;
    }

    /**
     * Sets the Event entity.
     */
    public function setEvent(Event $event)
    {
        $this->entity = $event;
    }
}
