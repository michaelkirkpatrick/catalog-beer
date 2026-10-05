<?php
/**
 * Cross-request cache (APCu) for values every page view would otherwise fetch
 * from the API: the navbar counts and the style taxonomy.
 *
 * Why not $_SESSION: a session cache is one copy per visitor, and initialize.php
 * only starts a session when the client already presents a cookie, so anonymous
 * visitors -- every crawler, i.e. most of the traffic -- never had a cache at
 * all. Over Sep 25-Oct 5 2026 the two count endpoints alone were ~1.5M API
 * self-calls for two slowly-changing numbers. APCu is one copy per PHP-FPM
 * master, shared by every request on the box.
 *
 * Keys are prefixed with the site and environment because the www pool serves
 * every PHP site on the box and APCu is per master, not per vhost.
 *
 * Degrades to "no cache" when the extension is missing (function_exists guard),
 * so a box without php-apcu renders exactly as before, just slower.
 */

function cacheKey(string $key): string {
    return 'catalogbeer:' . (defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown') . ':' . $key;
}

function cacheAvailable(): bool {
    static $ok = null;
    if ($ok === null) {
        $ok = function_exists('apcu_fetch') && function_exists('apcu_enabled') && apcu_enabled();
    }
    return $ok;
}

/** The cached value, or null on a miss (so never cache null). */
function cacheGet(string $key) {
    if (!cacheAvailable()) {
        return null;
    }
    $hit = false;
    $value = apcu_fetch(cacheKey($key), $hit);
    return $hit ? $value : null;
}

function cacheSet(string $key, $value, int $ttl): void {
    if (!cacheAvailable() || $value === null) {
        return;
    }
    apcu_store(cacheKey($key), $value, $ttl);
}

function cacheDelete(string $key): void {
    if (!cacheAvailable()) {
        return;
    }
    apcu_delete(cacheKey($key));
}
