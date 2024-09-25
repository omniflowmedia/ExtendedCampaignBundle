<?php

declare(strict_types=1);

namespace MauticPlugin\SurgeExtendedCampaignBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use MauticPlugin\SurgeExtendedCampaignBundle\DependencyInjection\Compiler\OverrideInterval;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SurgeExtendedCampaignBundle extends PluginBundleBase
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideInterval());
        parent::build($container);

    }
}
