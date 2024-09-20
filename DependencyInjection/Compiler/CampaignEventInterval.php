<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\DependencyInjection\Compiler;

use MauticPlugin\SurgeExtendedCampaignBundle\Executioner\UpdateCampaignEventInterval;
use MauticPlugin\SurgeExtendedCampaignBundle\Model\CustomEventModel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CampaignEventInterval implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('mautic.campaign.scheduler.interval')) {
            $container->getDefinition('mautic.campaign.scheduler.interval')
                ->setClass(UpdateCampaignEventInterval::class);
        }
    }
}
