<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GitHub repository (owner/name)
    |--------------------------------------------------------------------------
    |
    | When set, the platform uses the GitHub REST API "latest release" endpoint
    | to determine the canonical schema version for tenant migrations. Release
    | tags should use semver (e.g. v1.0.0). Leave empty to rely on APP_VERSION only.
    |
    */

    'repo' => env('GITHUB_REPO'),

    /*
    |--------------------------------------------------------------------------
    | Personal access token (optional)
    |--------------------------------------------------------------------------
    |
    | Required for private repositories. Use a fine-grained token with
    | Contents: Read-only on the repo, or classic token with repo scope.
    |
    */

    'token' => env('GITHUB_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | API response cache (seconds)
    |--------------------------------------------------------------------------
    */

    'cache_seconds' => (int) env('GITHUB_CACHE_SECONDS', 900),

    /*
    |--------------------------------------------------------------------------
    | CA bundle (optional)
    |--------------------------------------------------------------------------
    |
    | On Windows, PHP may lack a CA bundle and HTTPS to api.github.com fails with
    | cURL error 60. Download https://curl.se/ca/cacert.pem and set in php.ini:
    | curl.cainfo and openssl.cafile to that file path, then restart the server.
    |
    */

];
