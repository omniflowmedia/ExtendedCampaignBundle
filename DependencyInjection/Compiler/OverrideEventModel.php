<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\DependencyInjection\Compiler;

use MauticPlugin\SurgeExtendedCampaignBundle\Model\CustomEventModel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OverrideEventModel implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('mautic.campaign.model.event')) {
            $container->getDefinition('mautic.campaign.model.event')
                ->setClass(CustomEventModel::class);
        }
    }
}
