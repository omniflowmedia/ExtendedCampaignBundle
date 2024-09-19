<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\ScheduledEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Monolog\Logger;

class CustomScheduleEvent implements EventSubscriberInterface
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

  private function onCustomEventSchedule(ScheduledEvent $event)
  {
    $this->logger->error('onCampaignEventPostSave',['scheduledEvent'  => $event]);
  }
}
