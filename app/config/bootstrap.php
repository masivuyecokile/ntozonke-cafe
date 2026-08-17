<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../helpers/response.php';

/**
 * Simple project autoloader.
 * This loads controllers and models automatically when their class is used.
 */
spl_autoload_register(function (string $className): void {
    $directories = [
        __DIR__ . '/../controllers/',
        __DIR__ . '/../models/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});