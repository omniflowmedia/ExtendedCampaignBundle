<?php


namespace MauticPlugin\SurgeExtendedCampaignBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\SurgeExtendedCampaignBundle\Entity\CustomCampaign;
use Mautic\CampaignBundle\Model\CampaignModel;


class CustomCampaignModel extends FormModel
{
    /**
     * @var CampaignModel
     */
    protected $campaignModel;

    /**
     * CitrixModel constructor.
     */
    public function __construct(CampaignModel $campaignModel)
    {
        $this->campaignModel  = $campaignModel;
    }


    public function getEntity($id = null)
    {
        if (null === $id) {
            return new CustomCampaign();
        }

        return parent::getEntity($id);
    }


    public function getRepository()
    {
        return $this->em->getRepository(CustomCampaign::class);
    }
    
}
