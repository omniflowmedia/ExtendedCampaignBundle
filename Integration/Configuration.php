<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Integration;


use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\CoreBundle\Exception\BadConfigurationException;


class Configuration
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var string
     */
    private $typeOptions;
    

    /**
     * Configuration constructor.
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
    }


    public function isPublished()
    {
        $integration = $this->integrationHelper->getIntegrationObject('SurgeExtendedCampaign');

        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     *
     * @throws ConfigurationException
     */
    public function getTypeOptions()
    {
        $this->setConfiguration();

        return $this->typeOptions;
    }

    /**
     * @throws ConfigurationException
     */
    private function setConfiguration()
    {

        $integration = $this->integrationHelper->getIntegrationObject('SurgeExtendedCampaign');

        if (!$integration || !$integration->getIntegrationSettings()->getIsPublished()) {
            throw new BadConfigurationException();
        }

        $this->typeOptions = $integration->getIntegrationSettings()->getFeatureSettings()['type_options'];

    }
}
