<?php

namespace App\Filament\Helpers;

class RouterHelper
{
    public static function getRoute(
        string $resourceName,
        array $params = [],
        string $operation = 'edit',
        string $panel = 'admin',

    ): string {
        return route("filament.{$panel}.resources.{$resourceName}.{$operation}", $params);
    }
}
