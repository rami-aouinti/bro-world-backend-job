<?php

declare(strict_types=1);

namespace App\Resume\Application\Service;

/**
 * @internal Value object describing a resume resource.
 */
final class ResumeEntityDefinition
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
    public const TYPE_DATE = 'date';
    public const TYPE_MEDIA_COLLECTION = 'media_collection';

    /**
     * @param array<string, array{type: string, nullable?: bool}> $fields
     */
    public function __construct(
        public readonly string $resource,
        public readonly string $entityClass,
        public readonly string $serializationGroup,
        public readonly array $fields,
        public readonly bool $scopedByUser = true
    ) {
    }
}
