<?php

namespace Lernkarten\Courseware;

use Courseware\BlockTypes\BlockType;
use Opis\JsonSchema\Schema;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class LernkartenBlock extends BlockType
{
    public static function getType(): string
    {
        return 'lernkarten';
    }

    public static function getTitle(): string
    {
        return dgettext('lernkarten-plugin', 'Lernkarten');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernkarten-plugin',
            'Lernkarten aus dem eigenen Arbeitsplatz einbinden.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__ . '/LernkartenBlock.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['image'];
    }

    public static function getFileTypes(): array
    {
        return ['image'];
    }

    /**
     * Returns the decoded payload of the block associated with this instance.
     *
     * @return mixed the decoded payload
     */
    public function getPayload()
    {
        $decoded = $this->decodePayloadString($this->block['payload']);
        return $decoded;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        return $payload;
    }

    /**
     * get all files related to this block.
     *
     * @return \FileRef[] list of file references related to this block
     */
    public function getFiles(): array
    {
        return [];
    }
}
