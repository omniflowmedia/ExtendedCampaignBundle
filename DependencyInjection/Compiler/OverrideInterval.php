<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\DependencyInjection\Compiler;

use MauticPlugin\SurgeExtendedCampaignBundle\Executioner\CustomInterval;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideInterval implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('mautic.campaign.scheduler.interval')) {
            $container->getDefinition('mautic.campaign.scheduler.interval')
                ->setClass(CustomInterval::class);
        }
    }
}
