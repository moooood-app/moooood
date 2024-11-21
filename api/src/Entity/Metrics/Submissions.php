<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\SubmissionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubmissionsRepository::class)]
#[MetricsApiResource(metricsType: 'submissions')]
class Submissions implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;

    #[ORM\Column(type: Types::INTEGER)]
    public int $submissions;

    #[ORM\Column(type: Types::INTEGER)]
    public int $characterCount;

    #[ORM\Column(type: Types::INTEGER)]
    public int $wordCount;

    #[ORM\Column(type: Types::INTEGER)]
    public int $sentenceCount;
}
