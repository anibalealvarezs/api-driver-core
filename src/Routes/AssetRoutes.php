<?php

namespace Anibalealvarezs\ApiDriverCore\Routes;

use Anibalealvarezs\ApiDriverCore\Controllers\AssetsController;
use Symfony\Component\HttpFoundation\Request;

class AssetRoutes
{
    public static function get(): array
    {
        return [
            '/driver-assets/{driver}/{path}' => [
                'httpMethod' => 'GET',
                'callable' => function (...$args) {
                    $request = Request::createFromGlobals();
                    $request->attributes->set('driver', $args['driver'] ?? 'core');
                    $request->attributes->set('path', $args['path'] ?? '');
                    return (new AssetsController())->serve($request);
                },
                'public' => true,
                'html' => false,
                'admin' => false,
                'requirements' => [
                    'path' => '.+'
                ]
            ]
        ];
    }
}
