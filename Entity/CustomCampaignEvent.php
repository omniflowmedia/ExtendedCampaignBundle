<?php

declare(strict_types=1);

namespace MauticPlugin\SurgeExtendedCampaignBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;


class CustomCampaignEvent
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $triggerIntervalType;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder
            ->setTable('campaign_events_cstm')
            ->setCustomRepositoryClass(CustomCampaignEventRepository::class);

        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->option('unsigned', true)
            ->build();

        $builder
            ->createField('triggerIntervalType', 'string')
            ->build();


    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTriggerIntervalType(): string
    {
        return $this->triggerIntervalType;
    }

    public function setTriggerIntervalType(string $triggerIntervalType): void
    {
        $this->triggerIntervalType = $triggerIntervalType;
    }

}
