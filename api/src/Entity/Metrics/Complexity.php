<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\ComplexityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\MappedSuperclass(repositoryClass: ComplexityRepository::class)]
#[MetricsApiResource(metricsType: Processor::COMPLEXITY)]
class Complexity implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;
    use PartTrait;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The SMOG Index (Simple Measure of Gobbledygook) estimates the years of education needed to understand a text. Based on the number of complex words (three or more syllables).')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $smogIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The complexity rating, representing an aggregated complexity measure of the text.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $complexityRating;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Gunning Fog Index is a readability formula estimating the years of formal education needed to understand the text. Considers average sentence length and percentage of complex words.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $gunningFogIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Coleman-Liau Index estimates the grade level required to understand the text based on characters per word and sentences per 100 words.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $colemanLiauIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Flesch Reading Ease score indicates how easy or difficult a text is to read. Higher scores mean easier readability.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $fleschReadingEase;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Linsear Write Formula calculates readability based on the number of simple (one or two syllables) and complex (three or more syllables) words.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $linsearWriteFormula;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Readability Consensus score is a composite score averaging multiple readability formulas, such as Flesch Reading Ease and Gunning Fog Index.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $readabilityConsensus;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Flesch-Kincaid Grade Level indicates the years of formal education required to understand a text, based on syllables per word and words per sentence.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $fleschKincaidGradeLevel;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Automated Readability Index estimates the grade level required to understand the text, using characters per word and sentences per word.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $automatedReadabilityIndex;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The Dale-Chall Readability Score measures readability by considering the percentage of words not on a list of commonly understood words.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $daleChallReadabilityScore;
}
