<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle\Form\Type;

use Mautic\CampaignBundle\Form\Type\EventCanvasSettingsType;
use Mautic\CampaignBundle\Form\Type\EventType;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\Type\ButtonGroupType;
use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Mautic\CoreBundle\Form\Type\PropertiesTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EventType.
 */
class CustomEventType extends EventType
{
    use PropertiesTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $triggerIntervalType = (!empty($options['data']['triggerIntervalType'])) ? $options['data']['triggerIntervalType'] : 'na';

        $builder->add(
            'triggerIntervalType',
            ChoiceType::class,
            [
                'choices'     => [
                    'mautic.campaign.event.intervalunit.customchoice.n' => 'na',
                    'mautic.campaign.event.intervalunit.customchoice.i' => 'i',
                    'mautic.campaign.event.intervalunit.customchoice.h' => 'h',
                    'mautic.campaign.event.intervalunit.customchoice.d' => 'd',
                    'mautic.campaign.event.intervalunit.customchoice.w' => 'w',
                    'mautic.campaign.event.intervalunit.customchoice.m' => 'm',
                ],
                'multiple'          => false,
                'label_attr'        => ['class' => 'control-label'],
                'label'             => false,
                'attr'              => [
                    'class' => 'form-control',
                    'onchange' => 'Mautic.updateTriggerIntervalUnitOptions()',
                ],
                'placeholder' => false,
                'required'    => false,
                'data'        => $triggerIntervalType,
            ]
        );

        $triggerIntervalUnit = (!empty($options['data']['triggerIntervalUnit'])) ? $options['data']['triggerIntervalUnit'] : 'd';

        $triggerIntervalUntiOptionsMap = [
            'na' => [
                'mautic.campaign.event.intervalunit.choice.i' => 'i',
                'mautic.campaign.event.intervalunit.choice.h' => 'h',
                'mautic.campaign.event.intervalunit.choice.d' => 'd',
                'mautic.campaign.event.intervalunit.choice.m' => 'm',
                'mautic.campaign.event.intervalunit.choice.y' => 'y',
            ],
            'i' => [
                'mautic.campaign.event.intervalunit.choice.h' => 'h',
                'mautic.campaign.event.intervalunit.choice.d' => 'd',
                'mautic.campaign.event.intervalunit.choice.m' => 'm',
                'mautic.campaign.event.intervalunit.choice.y' => 'y',
            ],
            'h' => [
                'mautic.campaign.event.intervalunit.choice.d' => 'd',
                'mautic.campaign.event.intervalunit.choice.m' => 'm',
                'mautic.campaign.event.intervalunit.choice.y' => 'y',
            ],
            'd' => [
                'mautic.campaign.event.intervalunit.choice.m' => 'm',
                'mautic.campaign.event.intervalunit.choice.y' => 'y',
            ],
            'w' => [
                'mautic.campaign.event.intervalunit.choice.m' => 'm',
                'mautic.campaign.event.intervalunit.choice.y' => 'y',
            ],
            'm' => [
                'mautic.campaign.event.intervalunit.choice.y' => 'y',
            ],
        ];

        $options = [
            'mautic.campaign.event.intervalunit.choice.i' => 'i',
            'mautic.campaign.event.intervalunit.choice.h' => 'h',
            'mautic.campaign.event.intervalunit.choice.d' => 'd',
            'mautic.campaign.event.intervalunit.choice.m' => 'm',
            'mautic.campaign.event.intervalunit.choice.y' => 'y',
        ];

        if(!empty($triggerIntervalType) && isset($triggerIntervalUntiOptionsMap[$triggerIntervalType])){
            $options = $triggerIntervalUntiOptionsMap[$triggerIntervalType];
        }

        $builder->add(
            'triggerIntervalUnit',
            ChoiceType::class,
            [
                'choices'     => $options,
                'multiple'          => false,
                'label_attr'        => ['class' => 'control-label'],
                'label'             => false,
                'attr'              => [
                    'class' => 'form-control',
                ],
                'placeholder' => false,
                'required'    => false,
                'data'        => $triggerIntervalUnit,
            ]
        );
    }
}
