<?php

namespace App\DataFixtures;

use App\DataFixtures\Helpers\Metadata\MetadataHelperInterface;
use App\Entity\Entry;
use App\Entity\User;
use Faker\Generator;

final class EntryHelper
{
    /**
     * @var MetadataHelperInterface[]
     */
    private array $metadataHelpers = [];

    public function __construct(private readonly Generator $faker)
    {
        $this->metadataHelpers = [];
    }

    public function addMetadataHelper(MetadataHelperInterface $metadataHelper): self
    {
        $this->metadataHelpers[] = $metadataHelper;

        return $this;
    }

    public function provideEntry(User $user): Entry
    {
        $entry = (new Entry())
            ->setUser($user)
            ->setContent($this->faker->paragraph)
        ;

        foreach ($this->metadataHelpers as $metadataHelper) {
            $metadata = $metadataHelper->provideMetadata($entry);
            $entry->addMetadata($metadata);
        }

        return $entry;
    }
}
