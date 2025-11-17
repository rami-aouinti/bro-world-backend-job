<?php

declare(strict_types=1);

namespace App\Resume\Application\Service;

final class ResumeCacheKeyGenerator
{
    public const string LIST_TAG_PREFIX = 'resume.list.';
    public const string ITEM_TAG_PREFIX = 'resume.item.';

    public static function entityListKey(string $resource, ?string $userId): string
    {
        return sprintf('resume.%s.list.%s', $resource, $userId ?? 'global');
    }

    public static function entityItemKey(string $resource, string $entityId, ?string $userId): string
    {
        return sprintf('resume.%s.item.%s.%s', $resource, $entityId, $userId ?? 'global');
    }

    public static function entityListTag(string $resource, ?string $userId): string
    {
        return self::LIST_TAG_PREFIX . $resource . '.' . ($userId ?? 'global');
    }

    public static function entityItemTag(string $resource, string $entityId, ?string $userId): string
    {
        return self::ITEM_TAG_PREFIX . $resource . '.' . $entityId . '.' . ($userId ?? 'global');
    }
}
