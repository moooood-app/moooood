<?php

namespace App\Tests\Integration\Traits;

use Swaggest\JsonSchema\Schema;

trait ValidateJsonSchemaTrait
{
    public static function assertJsonSchemaIsValid(object $data, string $schema): void
    {
        $path = realpath("tests/json-schemas/{$schema}");
        if (false === $path) {
            self::fail("Schema file not found: {$schema}");
        }

        $schemaContent = file_get_contents($path);
        if (false === $schemaContent) {
            self::fail("Schema file could not be read: {$schema}");
        }

        $schema = Schema::import(json_decode($schemaContent));
        $schema->in($data);
    }
}
