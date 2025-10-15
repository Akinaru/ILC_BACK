<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | On lit la version depuis un fichier ".version" à la racine du projet.
    | Si le fichier n'existe pas, on retombe sur APP_VERSION (env) puis "0.0.0".
    |
    */
    'version' => (function () {
        $path = base_path('.version');
        if (is_file($path)) {
            $raw = @file_get_contents($path);
            if ($raw !== false) {
                return trim($raw);
            }
        }
        return env('APP_VERSION', '0.0.0');
    })(),
];