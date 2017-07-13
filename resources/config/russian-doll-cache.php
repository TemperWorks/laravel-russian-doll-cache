<?php

return [

    // Enable or disable partialcache alltogether
    'enabled' => 'true',

    // The name of the blade directive to register
    'directive' => 'cache',

    // The base key that used for cache items
    'key' => 'partialcache',

    // Use the cacheKeys from $mergeData objects if available?
    'object_cachekeys' => true,
];
