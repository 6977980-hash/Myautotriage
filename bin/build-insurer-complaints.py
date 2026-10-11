#!/usr/bin/env python3
"""Build assets/js/data/insurer-complaints.json from state regulators' open data.

Sources (both public open-data portals, no key needed):
  * Texas Department of Insurance, data.texas.gov
      pa9u-9s9w  complaint index and policies in force by company, auto line
      ubdr-4uff  every complaint, with reason and how it was resolved
  * New York DFS, data.ny.gov
      h2wd-9xfe  auto insurer complaint ranking (upheld complaints per $1M premium)

Run once a year after the new data is out:
    python3 bin/build-insurer-complaints.py 2025 2024
(the Texas year, then the New York filing year). The NAIC's own complaint
database is not used: its terms forbid redistributing the data.
"""
import collections
import datetime
import json
import os
import sys
import urllib.parse
import urllib.request

TX_YEAR = sys.argv[1] if len(sys.argv) > 1 else '2025'
NY_YEAR = sys.argv[2] if len(sys.argv) > 2 else '2024'
OUT = os.path.join(os.path.dirname(__file__), '..', 'assets', 'js', 'data', 'insurer-complaints.json')

# Brands and the companies (by NAIC company code) that sell under them.
# Only companies a brand owns and sells under its own name or a well-known
# sister brand; fronting carriers shared by many brands are left out.
GROUPS = [
    ('state-farm', 'State Farm', ['25178', '25143', '26816']),
    ('progressive', 'Progressive', ['29203', '11851', '24260', '16322', '24279', '42919', '32786']),
    ('geico', 'GEICO', ['35882', '22055', '22063', '29181', '27863', '14138']),
    ('allstate', 'Allstate', ['29335', '29688', '19240', '19232', '17230', '25712', '30210', '11252', '15130', '10071', '10072']),
    ('usaa', 'USAA', ['25941', '25968', '18600', '21253']),
    ('liberty-mutual', 'Liberty Mutual', ['19544', '23035', '23043', '36447', '33600', '33588', '19704', '11215']),
    ('farmers', 'Farmers', ['40169', '34339', '13938', '26298', '24392', '21687', '28673', '34789', '11185', '29254', '41513']),
    ('nationwide', 'Nationwide', ['26093', '10723', '23760', '25453', '23787']),
    ('travelers', 'Travelers', ['19046', '36137', '27998', '25682', '38130', '36145', '25674', '36161', '28188', '19070', '25615', '25623', '19062']),
    ('the-hartford', 'The Hartford', ['19682', '29424', '38288', '37478', '30104', '11000', '27120']),
    ('kemper', 'Kemper', ['13820', '22268', '10914', '16063', '10226', '25909', '40703']),
    ('mercury', 'Mercury', ['29394', '11908']),
    ('erie', 'Erie', ['26263', '16233']),
    ('amica', 'Amica', ['19976', '12287']),
    ('tesla', 'Tesla Insurance', ['24821']),
]

# How TDI recorded the outcome; any of these means the consumer got paid or
# the claim was settled after complaining.
PAID = {'Claim Settled', 'Additional Monies Received', 'Additional Payment Expected'}
CLAIM_REASONS = {'Cust Service Claim Handling', 'Delays (Claims Handling)', 'Unsatisfactory Settle/Offer', 'Denial Of Claim', 'Liability Dispute'}
REASON_LABELS = {
    'Cust Service Claim Handling': 'Poor claim handling or communication',
    'Delays (Claims Handling)': 'Claim delays',
    'Unsatisfactory Settle/Offer': 'Low settlement offer',
    'Denial Of Claim': 'Claim denied',
    'Liability Dispute': 'Disputed who was at fault',
    'Cust Srvc Policy Holder Srvcs': 'Policy service problems',
    'Refund Of Premium': 'Premium refund',
    'Premium and Rating': 'Premium or rating',
    'Delays (Policyholder Service)': 'Policy service delays',
    'Cancellation': 'Policy cancelled',
    'Use Of Clue Reports': 'Use of CLUE claim history reports',
    'Misrepresentation': 'Misrepresentation',
    'Non-Renewal': 'Policy not renewed',
}


def get(url, params):
    q = urllib.parse.urlencode(params)
    with urllib.request.urlopen(url + '?' + q, timeout=120) as r:
        return json.load(r)


def main():
    tx_idx = get('https://data.texas.gov/resource/pa9u-9s9w.json', {
        '$where': "type_cd='Automobile' AND year='%s'" % TX_YEAR, '$limit': 50000})
    tx_c = get('https://data.texas.gov/resource/ubdr-4uff.json', {
        '$select': 'respondent_id,reason,complaint_confirmed_code,disposition',
        '$where': "coverage_type='Automobile' AND received_date>='%s-01-01' AND received_date<'%d-01-01'" % (TX_YEAR, int(TX_YEAR) + 1),
        '$limit': 100000})
    ny = get('https://data.ny.gov/resource/h2wd-9xfe.json', {'$where': "filing_year='%s'" % NY_YEAR, '$limit': 50000})

    tx_total_c = sum(int(r.get('col1', 0)) for r in tx_idx)
    tx_total_p = sum(int(r.get('col2', 0)) for r in tx_idx)
    ny_total_u = sum(int(r.get('upheld_complaints', 0)) for r in ny)
    ny_total_p = sum(float(r.get('premiums_written_in_millions', 0)) for r in ny)

    by_org = collections.defaultdict(list)
    for c in tx_c:
        by_org[c.get('respondent_id')].append(c)

    def outcome_stats(rows):
        reasons = collections.Counter()
        paid = 0
        claim_rows = 0
        for c in rows:
            rs = [s.strip() for s in (c.get('reason') or '').split(';') if s.strip()]
            for s in set(rs):
                reasons[s] += 1
            if CLAIM_REASONS & set(rs):
                claim_rows += 1
                if PAID & {s.strip() for s in (c.get('disposition') or '').split(';')}:
                    paid += 1
        return {
            'complaints': len(rows),
            'confirmed': sum(1 for c in rows if c.get('complaint_confirmed_code') == 'Yes'),
            'claim_complaints': claim_rows,
            'claim_paid': paid,
            'reasons': [{'reason': REASON_LABELS.get(k, k), 'count': v} for k, v in reasons.most_common(5)],
        }

    groups = []
    for slug, name, codes in GROUPS:
        tx_rows = [r for r in tx_idx if r.get('naic_id') in codes]
        ny_rows = [r for r in ny if r.get('naic') in codes]
        g = {'slug': slug, 'name': name, 'companies': []}
        if tx_rows:
            c = sum(int(r.get('col1', 0)) for r in tx_rows)
            p = sum(int(r.get('col2', 0)) for r in tx_rows)
            g['tx'] = {
                'index_confirmed': c,
                'policies': p,
                'index': round((c / tx_total_c) / (p / tx_total_p), 2) if p else None,
            }
            rows = [x for r in tx_rows for x in by_org.get(r.get('org_id'), [])]
            g['tx'].update(outcome_stats(rows))
            g['companies'] += [{'state': 'TX', 'name': r['company_name'], 'naic': r['naic_id'], 'policies': int(r.get('col2', 0)), 'confirmed': int(r.get('col1', 0)), 'index': float(r.get('col3', 0))} for r in tx_rows]
        if ny_rows:
            u = sum(int(r.get('upheld_complaints', 0)) for r in ny_rows)
            t = sum(int(r.get('total_complaints', 0)) for r in ny_rows)
            p = sum(float(r.get('premiums_written_in_millions', 0)) for r in ny_rows)
            g['ny'] = {
                'upheld': u,
                'total': t,
                'premium_millions': round(p, 1),
                'ratio': round(u / p, 4) if p else None,
                'index': round((u / p) / (ny_total_u / ny_total_p), 2) if p else None,
            }
            g['companies'] += [{'state': 'NY', 'name': r['company_name'], 'naic': r['naic'], 'premium_millions': round(float(r.get('premiums_written_in_millions', 0)), 1), 'upheld': int(r.get('upheld_complaints', 0)), 'rank': int(r.get('rank', 0))} for r in ny_rows]
        groups.append(g)

    out = {
        'built': datetime.date.today().isoformat(),
        'tx_year': TX_YEAR,
        'ny_year': NY_YEAR,
        'tx_market': dict(outcome_stats(tx_c), policies=tx_total_p, confirmed_indexed=tx_total_c, companies=len(tx_idx)),
        'ny_market': {'upheld': ny_total_u, 'premium_millions': round(ny_total_p, 1), 'ratio': round(ny_total_u / ny_total_p, 4), 'companies': len(ny)},
        'groups': groups,
    }
    with open(OUT, 'w') as f:
        json.dump(out, f, indent=1)
        f.write('\n')
    for g in groups:
        print(g['name'].ljust(16), g.get('tx', {}).get('index'), g.get('ny', {}).get('index'), g.get('tx', {}).get('complaints'))


if __name__ == '__main__':
    main()
