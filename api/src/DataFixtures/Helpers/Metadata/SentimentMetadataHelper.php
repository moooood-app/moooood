<?php

namespace App\DataFixtures\Helpers\Metadata;

use App\Entity\EntryMetadata;
use App\Enum\Processor;
use Faker\Generator;

final readonly class SentimentMetadataHelper implements MetadataHelperInterface
{
    public function __construct(private readonly Generator $faker)
    {
    }

    public function provideMetadata(): EntryMetadata
    {
        return (new EntryMetadata())
            ->setProcessor(Processor::SENTIMENT)
            ->setMetadata([
                'compound' => $this->faker->randomFloat(16, -1, 1),
                'negative' => $this->faker->randomFloat(16, 0, 1),
                'positive' => $this->faker->randomFloat(16, 0, 1),
                'neutral' => $this->faker->randomFloat(16, 0, 1),
            ])
        ;
    }
}
