<?php

declare(strict_types=1);

namespace MauticPlugin\SurgeExtendedCampaignBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use MauticPlugin\SurgeExtendedCampaignBundle\DependencyInjection\Compiler\OverrideEventModel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SurgeExtendedCampaignBundle extends PluginBundleBase
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideEventModel());
        parent::build($container);
    }
}
