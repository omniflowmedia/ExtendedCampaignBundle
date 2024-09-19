<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;
use Mautic\CoreBundle\Form\Type\SortableListType;


class SurgeExtendedCampaignIntegration extends AbstractIntegration
{

    public const NAME         = 'SurgeExtendedCampaign';
    public const DISPLAY_NAME = 'Surge Extended Campaign Plugin';

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return self::DISPLAY_NAME;
    }

    public function getIcon(): string
    {
        return 'plugins/SurgeExtendedCampaignBundle/Assets/img/icon.png';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('features' == $formArea) {
            $builder->add(
                'type_options',
                SortableListType::class,
                [
                    'required'        => true,
                    'label'           => 'mautic.custom.campaigns.type_list',
                    'attr'            => [
                        'tooltip' => 'mautic.custom.campaigns.type_list.tooltip',
                    ],
                    'option_required' => true,
                    'with_labels'     => true,
                    'key_value_pairs' => true,
                ]
            );
        }
    }
}
