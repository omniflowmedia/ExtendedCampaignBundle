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
use Monolog\Logger;

class ControllerSubscriber implements EventSubscriberInterface
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
      KernelEvents::CONTROLLER => ['onKernelController', 0],
    ];
  }

  public function onKernelController(FilterControllerEvent $event)
  {

    $integration = $this->integrationHelper->getIntegrationObject('SurgeExtendedCampaign');

    if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
      return;
    }

    $request = $event->getRequest();
    $objectAction = $request->get('objectAction');

    if ('Mautic\CampaignBundle\Controller\CampaignController::executeAction' === $request->get('_controller')) {
      if (in_array($objectAction, ['edit', 'clone', 'new', 'delete', 'batchDelete'])) {
        $controller = 'MauticPlugin\SurgeExtendedCampaignBundle\Controller\CustomCampaignsController::executeAction';
        $this->resolveController($request, $controller, $event);
      }
    }

    if('Mautic\CampaignBundle\Controller\EventController::executeAction' === $request->get('_controller')) {
      if (in_array($objectAction, ['edit', 'new', 'delete'])) {
        $controller = "MauticPlugin\SurgeExtendedCampaignBundle\Controller\CustomEventController::executeAction";
        $this->resolveController($request, $controller, $event);
      }
    }
  }
  private function resolveController($request, $controller, $event)
  {
    $objectAction = $request->get('objectAction');
    $objectId          = $request->get('objectId');

    $request->attributes->add(
      [
        'objectAction'   => $objectAction,
        'objectId'          => $objectId,
        '_controller'   => $controller,
        '_route_params' => [
          'objectAction' => $objectAction,
          'objectId'        => $objectId,
        ],
      ]
    );

    $controller = $this->resolver->getController($request);
    $event->setController($controller);

    $controller = $event->getController();

    if (!is_array($controller)) {
      return;
    }

    $controller[0]->setRequest($request);

    // set the factory for easy use access throughout the controllers
    // @deprecated To be removed in 3.0
    $controller[0]->setFactory($this->factory);

    // set the user as well
    $controller[0]->setUser($this->userHelper->getUser());

    // and the core parameters helper
    $controller[0]->setCoreParametersHelper($this->coreParametersHelper);

    // and the dispatcher
    $controller[0]->setDispatcher($this->dispatcher);

    // and the translator
    $controller[0]->setTranslator($this->translator);

    // and the flash bag
    $controller[0]->setFlashBag($this->flashBag);

    //run any initialize functions
    $controller[0]->initialize($event);
  }


}
