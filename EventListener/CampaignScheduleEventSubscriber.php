<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Event\ScheduledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\CampaignBundle\Entity\LeadEventLog;
use Monolog\Logger;

class CampaignScheduleEventSubscriber implements EventSubscriberInterface
{
  /**
   * @var Logger
   */
  private $logger;

  /**
   * ControllerSubscriber constructor.
   */
  public function __construct(Logger $logger)
  {
    $this->logger = $logger;
  }


  public static function getSubscribedEvents()
  {
    return [
      CampaignEvents::ON_EVENT_SCHEDULED => ['onCustomEventSchedule', 0],
    ];
  }


  public function onCustomEventSchedule(ScheduledEvent $event)
  {
    $this->logger->error('onCampaignEventPostSave', ['scheduledEvent'  => $event]);
    /**
     * @var LeadEventLog $log
     */
    $log = $event->getLog();

    /**
     * @var Event $event
     */
    $event = $log->getEvent();

    if (null == $event) {
      return;
    }

    if ($event->getTriggerMode() == 'immediate') {
      return;
    }
  }
}
