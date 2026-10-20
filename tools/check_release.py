#!/usr/bin/env python3
"""Check the distributable code and translation keys without reading user data."""
from pathlib import Path
import json,re
root=Path(__file__).resolve().parents[1];ext=root/'xExtension-ContentStudio'
errors=[]
for p in ext.rglob('*'):
 if not p.is_file():continue
 text=p.read_text()
 forbidden=[r'host[.]docker[.]internal',r'\.ts\.net',r'/'+r'Users/',r'192\.168\.\d+\.\d+',r'10\.\d+\.\d+\.\d+',r'contentHub',r'Bitirme',r'Göstergeler',r'paper-status',r'news-clusters[.]json',r'api/market']
 for pattern in forbidden:
  if re.search(pattern,text,re.I):errors.append(f'{p.relative_to(root)} contains a deployment-specific or excluded reference')
 if p.suffix=='.json':json.loads(text)
base=json.loads((ext/'i18n/en.json').read_text())
for lang in ['tr','de','fr','es','zh']:
 d=json.loads((ext/f'i18n/{lang}.json').read_text())
 if set(d)!=set(base):errors.append(f'{lang} translation keys differ')
 if any(not isinstance(v,str) or not v.strip() for v in d.values()):errors.append(f'{lang} contains an empty translation')
for forbidden in ['Controllers/contentHubController.php','static/market.js','static/market.css','paper-status.json','news-clusters.json']:
 if (ext/forbidden).exists():errors.append('Excluded module is present')
for required in ['README.md','README.tr.md','LICENSE','NOTICE','SECURITY.md','CHANGELOG.md','xExtension-ContentStudio/metadata.json']:
 if not(root/required).is_file():errors.append(f'Missing {required}')
assert not errors,'\n'.join(errors)
print(f'Release checks passed: {len(list(ext.rglob("*")))} package paths, six complete locales, no deployment-specific or excluded modules.')
