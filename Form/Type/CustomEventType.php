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

    if (in_array($options['data']['eventType'], ['action', 'condition'])) {

      $data = (!isset($options['data']['triggerInterval']) || '' === $options['data']['triggerInterval']
        || null === $options['data']['triggerInterval']) ? 1 : (int) $options['data']['triggerInterval'];
      $builder->add(
        'triggerInterval',
        NumberType::class,
        [
          'label' => false,
          'attr'  => [
            'class'    => 'form-control',
            'preaddon' => 'symbol-hashtag',
            'onchange' => 'Mautic.updateTriggerIntervalUnitOptions()',
          ],
          'data'  => $data,
        ]
      );

      $triggerIntervalType = (!empty($options['data']['triggerIntervalType'])) ? $options['data']['triggerIntervalType'] : 'minute';

      $builder->add(
        'triggerIntervalType',
        ChoiceType::class,
        [
          'choices'     => [
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

      $triggerIntervalStatus = (!empty($options['data']['triggerIntervalStatus'])) ? $options['data']['triggerIntervalStatus'] : 'wait';

      $builder->add(
        'triggerIntervalStatus',
        ChoiceType::class,
        [
          'choices'     => [
            'mautic.campaign.event.intervalstatus.customchoice.wait' => 'wait',
            'mautic.campaign.event.intervalstatus.customchoice.wait_until' => 'wait_until',
          ],
          'multiple'          => false,
          'label_attr'        => ['class' => 'control-label'],
          'label'             => false,
          'attr'              => [
            'class' => 'form-control',
            'onchange' => 'Mautic.updateTriggerIntervalOptions()',
          ],
          'placeholder' => false,
          'required'    => false,
          'data'        => $triggerIntervalStatus,
        ]
      );

      $triggerIntervalUnit = (!empty($options['data']['triggerIntervalUnit'])) ? $options['data']['triggerIntervalUnit'] : 'd';

      $options = [
        'mautic.campaign.event.intervalunit.choice.i' => 'i',
        'mautic.campaign.event.intervalunit.choice.h' => 'h',
        'mautic.campaign.event.intervalunit.choice.d' => 'd',
        'mautic.campaign.event.intervalunit.choice.m' => 'm',
        'mautic.campaign.event.intervalunit.choice.y' => 'y',
      ];

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
            'data-onload-callback' => 'updateTriggerIntervalUnitOptions',
          ],
          'placeholder' => false,
          'required'    => false,
          'data'        => $triggerIntervalUnit,
        ]
      );
    }
  }
}
