<?php

declare(strict_types=1);

namespace MauticPlugin\SurgeExtendedCampaignBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;


class CustomCampaign
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type = '';

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder
            ->setTable('campaigns_cstm')
            ->setCustomRepositoryClass(CustomCampaignRepository::class);

        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->option('unsigned', true)
            ->build();

        $builder
            ->createField('type', 'string')
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

}
