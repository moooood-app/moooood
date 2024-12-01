<?php

namespace App\DataFixtures\Helpers\Metadata;

use App\Entity\EntryMetadata;
use App\Enum\Processor;
use Faker\Generator;

final readonly class EmotionsMetadataHelper implements MetadataHelperInterface
{
    public function __construct(private readonly Generator $faker)
    {
    }

    public function provideMetadata(): EntryMetadata
    {
        return (new EntryMetadata())
            ->setProcessor(Processor::EMOTIONS)
            ->setMetadata([
                'joy' => $this->faker->randomFloat(16, 0, 1),
                'fear' => $this->faker->randomFloat(16, 0, 1),
                'love' => $this->faker->randomFloat(16, 0, 1),
                'anger' => $this->faker->randomFloat(16, 0, 1),
                'grief' => $this->faker->randomFloat(16, 0, 1),
                'pride' => $this->faker->randomFloat(16, 0, 1),
                'caring' => $this->faker->randomFloat(16, 0, 1),
                'desire' => $this->faker->randomFloat(16, 0, 1),
                'relief' => $this->faker->randomFloat(16, 0, 1),
                'disgust' => $this->faker->randomFloat(16, 0, 1),
                'neutral' => $this->faker->randomFloat(16, 0, 1),
                'remorse' => $this->faker->randomFloat(16, 0, 1),
                'sadness' => $this->faker->randomFloat(16, 0, 1),
                'approval' => $this->faker->randomFloat(16, 0, 1),
                'optimism' => $this->faker->randomFloat(16, 0, 1),
                'surprise' => $this->faker->randomFloat(16, 0, 1),
                'amusement' => $this->faker->randomFloat(16, 0, 1),
                'annoyance' => $this->faker->randomFloat(16, 0, 1),
                'confusion' => $this->faker->randomFloat(16, 0, 1),
                'curiosity' => $this->faker->randomFloat(16, 0, 1),
                'gratitude' => $this->faker->randomFloat(16, 0, 1),
                'admiration' => $this->faker->randomFloat(16, 0, 1),
                'excitement' => $this->faker->randomFloat(16, 0, 1),
                'disapproval' => $this->faker->randomFloat(16, 0, 1),
                'nervousness' => $this->faker->randomFloat(16, 0, 1),
                'realization' => $this->faker->randomFloat(16, 0, 1),
                'embarrassment' => $this->faker->randomFloat(16, 0, 1),
                'disappointment' => $this->faker->randomFloat(16, 0, 1),
            ])
        ;
    }
}
