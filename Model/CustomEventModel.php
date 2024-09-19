<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Model;

use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Model\EventModel;
use MauticPlugin\SurgeExtendedCampaignBundle\Event\CampaignEventEvent;
use MauticPlugin\SurgeExtendedCampaignBundle\ExtendedCampaignEvents;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\DependencyInjection\Container;


class CustomEventModel extends EventModel {

  protected function dispatchEvent($action, &$entity, $isNew = false, \Symfony\Component\EventDispatcher\Event $event = null)
  {
      $this->logger->error('Dispatching event ' );
      if (!$entity instanceof Event) {
          throw new MethodNotAllowedHttpException(['Event']);
      }
      
      switch ($action) {
          case 'pre_save':
              $name = ExtendedCampaignEvents::CAMPAIGN_EVENT_PRE_SAVE;
              break;
          case 'post_save':
              $name = ExtendedCampaignEvents::CAMPAIGN_EVENT_POST_SAVE;
              break;
          case 'pre_delete':
              $name = ExtendedCampaignEvents::CAMPAIGN_EVENT_PRE_DELETE;
              break;
          case 'post_delete':
              $name = ExtendedCampaignEvents::CAMPAIGN_EVENT_POST_DELETE;
              break;
          default:
              return null;
      }

      if ($this->dispatcher->hasListeners($name)) {
          if (empty($event)) {
              $event = new CampaignEventEvent($entity, $isNew);
          }
          $this->dispatcher->dispatch($name, $event);

          return $event;
      } else {
          return null;
      }
  }
}
