<?php
/**
 * Versioned asset URLs — cache-busting without a build step.
 *
 * Local stylesheets and scripts are served with a one-year immutable
 * Cache-Control (see the .htaccess block keyed on the `v` query param), which is
 * only safe because the URL changes whenever the file does: the version token is
 * the file's mtime, so editing an asset invalidates every cached copy on the next
 * deploy and nothing else does.
 *
 * mtime rather than a content hash because deploy.sh runs `rsync -azOi`, and -a
 * implies -t: mtimes are preserved on transfer, so the token is derived from the
 * local file and is identical on staging and production. A hash would additionally
 * survive a touch-without-edit (a fresh `git clone`, which doesn't preserve mtimes,
 * shifts every token once) — but that costs a full read per file per request where
 * a stat is enough, to save an occasional wasted download.
 *
 * The .htaccess rule is the other half of this and only fires on `?v=<digits>`.
 * An asset linked WITHOUT going through here still works, but falls back to a
 * one-hour cache — so it self-heals rather than pinning a stale copy for a year.
 *
 * Only pass LOCAL, web-absolute paths through these helpers. CDN URLs (Bootstrap,
 * Algolia) are already versioned in their path and are left alone; a non-local
 * path is returned untouched anyway (see assetUrl).
 *
 * Usage:
 *   echo cssTag('/assets/css/styles-pages.css');
 *   echo jsTag('/assets/js/guided-style.js');
 *   echo assetUrl('/assets/js/guided-style.js');   // "/assets/js/guided-style.js?v=1753210000"
 */

/**
 * Append the file's mtime as a version token.
 *
 * A path that doesn't resolve to a file on disk is returned untouched: a typo, or
 * an off-server URL, degrades to an unversioned link rather than an invented one.
 *
 * @param string $path  Web-absolute path ("/assets/css/styles-pages.css")
 * @return string       The same path with ?v=<mtime>, or unchanged if not on disk
 */
function assetUrl(string $path): string {
    $file = ROOT . $path;

    if (!is_file($file)) {
        return $path;
    }

    $mtime = filemtime($file);

    return $mtime === false ? $path : $path . '?v=' . $mtime;
}

/**
 * A complete versioned <link rel="stylesheet"> tag.
 *
 * @param string $path  Web-absolute path to a stylesheet
 * @return string       The tag, with the href escaped for HTML
 */
function cssTag(string $path): string {
    return '<link rel="stylesheet" href="' . htmlspecialchars(assetUrl($path)) . '">';
}

/**
 * A complete versioned <script src> tag.
 *
 * @param string $path  Web-absolute path to a script
 * @return string       The tag, with the src escaped for HTML
 */
function jsTag(string $path): string {
    return '<script src="' . htmlspecialchars(assetUrl($path)) . '"></script>';
}
