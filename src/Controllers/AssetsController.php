<?php

namespace Anibalealvarezs\ApiDriverCore\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AssetsController
{
    public function serve(Request $request): Response
    {
        $path = $request->attributes->get('path');
        $driverName = $request->attributes->get('driver');

        // Determine base directory
        if (!$driverName || $driverName === 'core') {
            $baseDir = dirname(__DIR__, 2) . '/assets';
        } else {
            // Find driver in registry to get class, then use reflection to find path
            $registry = \Anibalealvarezs\ApiDriverCore\Drivers\DriverFactory::getRegistry();
            $driverClass = null;
            
            // Try specific driver match
            if (isset($registry[$driverName])) {
                $driverClass = $registry[$driverName]['driver'];
            } else {
                // Try fuzzy match (e.g. facebook -> facebook_marketing)
                foreach ($registry as $chan => $cfg) {
                    if (str_contains($chan, $driverName)) {
                        $driverClass = $cfg['driver'];
                        break;
                    }
                }
            }

            if (!$driverClass || !class_exists($driverClass)) {
                return new Response('Driver not found', 404);
            }

            $reflection = new \ReflectionClass($driverClass);
            $baseDir = dirname($reflection->getFileName(), 2) . '/assets';
        }

        $fullPath = realpath($baseDir . '/' . $path);

        if (!$fullPath || !str_starts_with($fullPath, realpath($baseDir)) || !is_file($fullPath)) {
            return new Response('File not found', 404);
        }

        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
        ];

        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';

        return new Response(file_get_contents($fullPath), 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }
}
