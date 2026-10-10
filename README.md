# MyAutoTriage

WordPress theme for myautotriage.com. Files in the root of the `main` branch are deployed by Hostinger Git deployment to `public_html/wp-content/themes/myautotriage`, so anything merged to `main` goes live.

## After each deploy

- Purge the CDN cache in hPanel (Websites → Performance → CDN → Flush cache). The theme now sends `Cache-Control: public, max-age=3600` for HTML, but pages cached before that change can stay for up to 7 days.
- Nothing to do for Bing/IndexNow: the first request after a new theme version submits every URL (see `inc/indexnow.php`). The key file is served at `/<key>.txt`.
