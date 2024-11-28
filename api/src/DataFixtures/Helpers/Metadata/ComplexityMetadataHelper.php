<?php

namespace App\DataFixtures\Helpers\Metadata;

use App\Entity\EntryMetadata;
use App\Enum\Processor;
use Faker\Generator;

final readonly class ComplexityMetadataHelper implements MetadataHelperInterface
{
    public function __construct(private readonly Generator $faker)
    {
    }

    public function provideMetadata(): EntryMetadata
    {
        return (new EntryMetadata())
            ->setProcessor(Processor::COMPLEXITY)
            ->setMetadata([
                'complexity_rating' => $this->faker->randomFloat(2, 0, 100), // Computed average complexity rating
                'smog_index' => $this->faker->randomFloat(2, 6, 20), // Typically ranges from 6 to 20+
                'gunning_fog_index' => $this->faker->randomFloat(2, 6, 20), // Typically ranges from 6 to 20+
                'coleman_liau_index' => $this->faker->randomFloat(2, -3, 12), // Typically ranges from -3 to 12
                'flesch_reading_ease' => $this->faker->randomFloat(2, 0, 100), // 0 (most difficult) to 100 (easiest)
                'linsear_write_formula' => $this->faker->randomFloat(2, 0, 20), // Typically ranges from 0 to 20+
                'readability_consensus' => $this->faker->randomFloat(2, 0, 100), // Typically ranges from 0 to 100
                'flesch_kincaid_grade_level' => $this->faker->randomFloat(2, 0, 12), // Grade levels, e.g., 0 to 12
                'automated_readability_index' => $this->faker->randomFloat(2, 0, 12), // Grade levels, e.g., 0 to 12
                'dale_chall_readability_score' => $this->faker->randomFloat(2, 4, 16), // Typically 4 to 16 for grade levels
            ])
        ;
    }
}
