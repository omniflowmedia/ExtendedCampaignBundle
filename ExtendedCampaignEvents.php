<?php

namespace MauticPlugin\SurgeExtendedCampaignBundle;

/**
 * Class CampaignEvents
 * Events available for CampaignBundle.
 */
final class ExtendedCampaignEvents
{
    /**
     * The mautic.campaign_pre_save event is dispatched right before a form is persisted.
     *
     * The event listener receives a
     * Mautic\CampaignBundle\Event\CampaignEvent instance.
     *
     * @var string
     */
    const CAMPAIGN_EVENT_PRE_SAVE = 'mautic.campaign_event_pre_save';

    /**
     * The mautic.campaign_post_save event is dispatched right after a form is persisted.
     *
     * The event listener receives a
     * Mautic\CampaignBundle\Event\CampaignEvent instance.
     *
     * @var string
     */
    const CAMPAIGN_EVENT_POST_SAVE = 'mautic.campaign_event_post_save';

    /**
     * The mautic.campaign_pre_delete event is dispatched before a form is deleted.
     *
     * The event listener receives a
     * Mautic\CampaignBundle\Event\CampaignEvent instance.
     *
     * @var string
     */
    const CAMPAIGN_EVENT_PRE_DELETE = 'mautic.campaign_event_pre_delete';

    /**
     * The mautic.campaign_post_delete event is dispatched after a form is deleted.
     *
     * The event listener receives a
     * Mautic\CampaignBundle\Event\CampaignEvent instance.
     *
     * @var string
     */
    const CAMPAIGN_EVENT_POST_DELETE = 'mautic.campaign_event_post_delete';
}
