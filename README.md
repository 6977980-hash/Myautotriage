# MyAutoTriage

WordPress theme for myautotriage.com. Files in the root of the `main` branch are deployed by Hostinger Git deployment to `public_html/wp-content/themes/myautotriage`, so anything merged to `main` goes live.

## After each deploy

- Purge the CDN cache in hPanel (Websites → Performance → CDN → Flush cache). The theme now sends `Cache-Control: public, max-age=3600` for HTML, but pages cached before that change can stay for up to 7 days.
- Nothing to do for Bing/IndexNow: the first request after a new theme version submits every URL (see `inc/indexnow.php`). The key file is served at `/<key>.txt`.

## Claim outcome survey

New survey answers wait in WP Admin → Tools → Claim outcomes until you approve them; only approved, settled answers count toward the Lowball Index. Delete anything that looks scripted or made up. The survey pauses itself at 100 answers waiting for review.

## Before each pull request

- If a page template, the script or data file it loads, or the `inc/` file that writes its content changed, commit that change, then run `python3 bin/build-content-dates.py` and commit `inc/content-dates.json`. The sitemap's `<lastmod>` for theme-built pages comes from that file, because the deploy resets every file's modified time on the server.
- When you re-check a data file in `assets/js/data/`, set its `"reviewed"` date. State pages show the newest of the three.
