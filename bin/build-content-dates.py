#!/usr/bin/env python3
"""Build inc/content-dates.json: when each theme-built page last really changed.

Hostinger's Git deploy rewrites every theme file, so filemtime() on the server
is just the deploy time. Instead this reads the git history: for each page
template, the newest commit that touched the template itself, the scripts and
data files it loads (any assets/js/... path named in it), or the inc/ file that
writes its content. inc/sitemap.php uses the result for the sitemap <lastmod>.

Run after committing a change to a template, its script or its data:
    python3 bin/build-content-dates.py
and commit inc/content-dates.json with the next commit.
"""
import datetime
import glob
import json
import os
import re
import subprocess

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
OUT = os.path.join(ROOT, 'inc', 'content-dates.json')

# Pages whose text is written by an inc/ file rather than the template.
EXTRA = {
    'page-templates/template-state-hub.php': ['inc/state-laws.php'],
    'page-templates/template-state-laws.php': ['inc/state-laws.php', 'assets/js/data/diminished-value-rules.json'],
    'page-templates/template-dv-by-state.php': ['inc/state-laws.php', 'assets/js/data/diminished-value-rules.json'],
    'page-templates/template-insurer-hub.php': ['inc/insurer-complaints.php'],
    'page-templates/template-insurer.php': ['inc/insurer-complaints.php'],
    'page-templates/template-adjuster-hub.php': ['inc/adjuster-phrases.php'],
    'page-templates/template-adjuster-phrase.php': ['inc/adjuster-phrases.php'],
    'page-templates/template-claim-triage.php': ['inc/claim-triage.php'],
    'page-templates/template-lowball-index.php': ['inc/claim-outcomes.php'],
    'page-templates/template-outcome-survey.php': ['inc/claim-outcomes.php'],
}
ASSET = re.compile(r"assets/js/[\w/.-]+\.(?:js|json)")


def last_commit(paths):
    out = subprocess.run(['git', 'log', '-1', '--format=%ct', '--'] + paths,
                         cwd=ROOT, capture_output=True, text=True, check=True).stdout.strip()
    return int(out) if out else 0


def main():
    templates = sorted(os.path.relpath(p, ROOT) for p in glob.glob(os.path.join(ROOT, 'page-templates', '*.php')))
    templates.append('front-page.php')
    dates = {}
    for tpl in templates:
        deps = set()
        for src in [tpl] + EXTRA.get(tpl, []):
            with open(os.path.join(ROOT, src), encoding='utf-8') as f:
                deps.update(ASSET.findall(f.read()))
        deps = [tpl] + EXTRA.get(tpl, []) + sorted(d for d in deps if os.path.exists(os.path.join(ROOT, d)))
        ts = last_commit(deps)
        if ts:
            dates[tpl] = datetime.datetime.fromtimestamp(ts, datetime.timezone.utc).strftime('%Y-%m-%dT%H:%M:%S+00:00')
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(dates, f, indent='\t', sort_keys=True)
        f.write('\n')
    print('Wrote %d dates to %s' % (len(dates), os.path.relpath(OUT, ROOT)))


if __name__ == '__main__':
    main()
