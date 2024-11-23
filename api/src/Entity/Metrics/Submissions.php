<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\SubmissionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass(repositoryClass: SubmissionsRepository::class)]
#[MetricsApiResource(metricsType: 'submissions')]
class Submissions implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The number of submissions')]
    public int $submissions;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The total number of characters in all submissions')]
    public int $characterCount;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The total number of words in all submissions')]
    public int $wordCount;

    #[ORM\Column(type: Types::INTEGER)]
    #[ApiProperty(required: true, description: 'The total number of sentences in all submissions')]
    public int $sentenceCount;
}
