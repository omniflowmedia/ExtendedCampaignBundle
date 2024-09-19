<?php


namespace MauticPlugin\SurgeExtendedCampaignBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\SurgeExtendedCampaignBundle\Entity\CustomCampaignEvent;
use Mautic\CampaignBundle\Model\EventModel;

class CustomCampaignEventModel extends FormModel
{
    /**
     * @var EventModel
     */
    protected $customCampaignFieldModel;

    /**
     * CitrixModel constructor.
     */
    public function __construct(EventModel $customCampaignFieldModel)
    {
        $this->customCampaignFieldModel  = $customCampaignFieldModel;
    }


    public function getEntity($id = null)
    {
        if (null === $id) {
            return new CustomCampaignEvent();
        }

        return parent::getEntity($id);
    }


    public function getRepository()
    {
        return $this->em->getRepository(CustomCampaignEvent::class);
    }
    
}
