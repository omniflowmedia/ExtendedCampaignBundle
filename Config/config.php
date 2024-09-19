<?php

declare(strict_types=1);

return [
  'name'        => 'Campiagns Extend',
  'description' => 'Extended Campiagns to add custom functionality',
  'version'     => '1.0.0',
  'author'      => 'Surge.Media',
  'services'    => [
    'events' => [
      'extendedcampaigns.event.controller.subscriber' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\EventListener\ControllerSubscriber::class,
        'arguments' => [
          'controller_resolver',
          'mautic.helper.user',
          'mautic.helper.core_parameters',
          'event_dispatcher',
          'translator',
          'mautic.factory',
          'mautic.core.service.flashbag',
          'mautic.helper.integration',
          'monolog.logger.mautic'
        ],
      ],
      'extendedcampaigns.event.customevent.subscriber' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\EventListener\CustomEventSubscriber::class,
        'arguments' => [
          'controller_resolver',
          'mautic.helper.user',
          'mautic.helper.core_parameters',
          'event_dispatcher',
          'translator',
          'mautic.factory',
          'mautic.core.service.flashbag',
          'mautic.helper.integration',
          'monolog.logger.mautic'
        ],
      ],
      'extendedcampaigns.event.customscheduleevent.subscriber' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\EventListener\CustomScheduleEvent::class,
        'arguments' => [
          'monolog.logger.mautic'
        ],
      ],
    ],
    'models' => [
      'mautic.extendedcampaigns.model.customcampaigns' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\Model\CustomCampaignModel::class,
        'arguments' => [
          'mautic.campaign.model.campaign',
        ],
      ],
      'mautic.extendedcampaigns.model.customcampaignevents' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\Model\CustomEventModel::class,
        'arguments' => [
        ],
      ],
    ],
    'forms' => [
      'mautic.form.type.extendedcampaigns.campaigns' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\Form\Type\CustomCampaignType::class,
        'arguments' => [
          'mautic.security',
          'translator',
          'mautic.extendedcampaigns.configuration',
        ],
      ],
      'mautic.form.type.extendedcampaigns.campaignsevent' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\Form\Type\CustomEventType::class,
        'arguments' => [
          'mautic.security',
          'translator',
        ],
      ],
    ],
    'other'        => [
      'mautic.extendedcampaigns.configuration' => [
        'class'        => \MauticPlugin\SurgeExtendedCampaignBundle\Integration\Configuration::class,
        'arguments'    => [
          'mautic.helper.integration',
        ],
      ],
    ],
    'integrations' => [
      'mautic.integration.surgeextendedcampaign' => [
        'class'     => \MauticPlugin\SurgeExtendedCampaignBundle\Integration\SurgeExtendedCampaignIntegration::class,
        'arguments' => [
          'event_dispatcher',
          'mautic.helper.cache_storage',
          'doctrine.orm.entity_manager',
          'session',
          'request_stack',
          'router',
          'translator',
          'logger',
          'mautic.helper.encryption',
          'mautic.lead.model.lead',
          'mautic.lead.model.company',
          'mautic.helper.paths',
          'mautic.core.model.notification',
          'mautic.lead.model.field',
          'mautic.plugin.model.integration_entity',
          'mautic.lead.model.dnc',
        ],
      ],
    ],
  ],
];
