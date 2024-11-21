<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\ComplexityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComplexityRepository::class)]
#[MetricsApiResource(metricsType: Processor::COMPLEXITY)]
class Complexity implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $smogIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $complexityRating;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $gunningFogIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $colemanLiauIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $fleschReadingEase;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $linsearWriteFormula;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $readabilityConsensus;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $fleschKincaidGradeLevel;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $automatedReadabilityIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $daleChallReadabilityScore;
}
