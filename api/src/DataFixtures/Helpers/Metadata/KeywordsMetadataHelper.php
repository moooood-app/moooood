<?php

namespace App\DataFixtures\Helpers\Metadata;

use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Enum\Processor;
use Faker\Generator;

final readonly class KeywordsMetadataHelper implements MetadataHelperInterface
{
    public function __construct(private readonly Generator $faker)
    {
    }

    public function provideMetadata(Entry $entry): EntryMetadata
    {
        $keywords = [];
        for ($j = 1; $j <= 5; ++$j) {
            $keywords[] = [
                'keyword' => $this->faker->words(2, true),
                'score' => $this->faker->randomFloat(4, 0, 1),
            ];
        }

        for ($k = 1; $k <= 10; ++$k) {
            $keywords[] = [
                'keyword' => $this->faker->word(),
                'score' => $this->faker->randomFloat(4, 0, 1),
            ];
        }

        return (new EntryMetadata())
            ->setEntry($entry)
            ->setProcessor(Processor::KEYWORDS)
            ->setMetadata($keywords)
        ;
    }
}
