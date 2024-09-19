<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\CoreBundle\Service\FlashBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\SurgeExtendedCampaignBundle\Event\CampaignEventEvent;
use MauticPlugin\SurgeExtendedCampaignBundle\ExtendedCampaignEvents;
use Monolog\Logger;

class CustomEventSubscriber implements EventSubscriberInterface
{
  /**
   * @var UserHelper
   */
  private $userHelper;

  /**
   * @var CoreParametersHelper
   */
  private $coreParametersHelper;

  /**
   * @var EventDispatcherInterface
   */
  private $dispatcher;

  /**
   * @var TranslatorInterface
   */
  private $translator;
  /**
   * @var ControllerResolverInterface
   */
  private $resolver;

  /**
   * @var MauticFactory
   */
  private $factory;

  /**
   * @var FlashBag
   */
  private $flashBag;

  /**
   * @var IntegrationHelper
   */
  private $integrationHelper;

  /**
   * @var Logger
   */
  private $logger;

  /**
   * ControllerSubscriber constructor.
   */
  public function __construct(ControllerResolverInterface $resolver, UserHelper $userHelper, CoreParametersHelper $coreParametersHelper, EventDispatcherInterface $dispatcher, TranslatorInterface $translator, MauticFactory $factory, FlashBag $flashBag, IntegrationHelper $integrationHelper,Logger $logger)
  {
    $this->resolver             = $resolver;
    $this->factory              = $factory;
    $this->userHelper           = $userHelper;
    $this->coreParametersHelper = $coreParametersHelper;
    $this->dispatcher           = $dispatcher;
    $this->translator           = $translator;
    $this->flashBag             = $flashBag;
    $this->integrationHelper    = $integrationHelper;
    $this->logger               = $logger;
  }


  public static function getSubscribedEvents()
  {
    return [
      ExtendedCampaignEvents::CAMPAIGN_EVENT_POST_SAVE => ['onCampaignEventPostSave', 0],
    ];
  }

  private function onCampaignEventPostSave(CampaignEventEvent $event)
  {
    $this->logger->error('onCampaignEventPostSave');
  }
}
