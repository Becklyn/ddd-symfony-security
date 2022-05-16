<?php declare(strict_types=1);

use Becklyn\Rector\Symfony\ReplaceControllerThisGetWithThisContainerGet;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    $rectorConfig->rule(TypedPropertyRector::class);
    $rectorConfig->rule(ReplaceControllerThisGetWithThisContainerGet::class);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::PHP_81,
        LevelSetList::UP_TO_PHP_81,
        SymfonyLevelSetList::UP_TO_SYMFONY_60,
        SymfonySetList::SYMFONY_CODE_QUALITY,
    ]);
};
