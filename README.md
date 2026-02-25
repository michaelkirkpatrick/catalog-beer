# Catalog.beer

This repository contains the code that powers the web version of [Catalog.beer](https://catalog.beer).

The web front-end interacts with the Catalog.beer database via the [Catalog.beer API](https://github.com/michaelkirkpatrick/catalog-beer-api).

Comments, issues and pull requests welcome.

-Michael

Michael Kirkpatrick  
Founder, Catalog.beer

## Cron Jobs

**Sitemap generation** — Regenerates `sitemap.xml` from the API. Run weekly or as needed:

```
0 4 * * 1 php /var/www/html/catalog.beer/public_html/generate-sitemap.php production
```

For staging: `php /var/www/html/staging.catalog.beer/public_html/generate-sitemap.php staging`

## See Also

* [Catalog.beer API - GitHub](https://github.com/michaelkirkpatrick/catalog-beer-api)
* [Catalog.beer MySQL Schema - GitHub](https://github.com/michaelkirkpatrick/catalog-beer-mysql)
