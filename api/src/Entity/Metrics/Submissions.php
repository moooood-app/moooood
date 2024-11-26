<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\SubmissionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\MappedSuperclass(repositoryClass: SubmissionsRepository::class)]
#[MetricsApiResource(metricsType: 'submissions')]
class Submissions implements MetricsIdentifierInterface
{
    use MetricsPropertiesTrait;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The number of submissions')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public int $submissions;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The total number of characters in all submissions')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public int $characterCount;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The total number of words in all submissions')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public int $wordCount;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The total number of sentences in all submissions')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public int $sentenceCount;
}
