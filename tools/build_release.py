#!/usr/bin/env python3
"""Create an install ZIP and a source ZIP; no deployment state is read."""
from pathlib import Path
from zipfile import ZipFile,ZIP_DEFLATED
import subprocess,sys,hashlib,json
root=Path(__file__).resolve().parents[1]
subprocess.run([sys.executable,str(root/'tools/check_release.py')],check=True)
version=json.loads((root/'xExtension-ContentStudio/metadata.json').read_text())['version']
dist=root/'dist';dist.mkdir(exist_ok=True)
install=[p for p in (root/'xExtension-ContentStudio').rglob('*') if p.is_file()]+[p for p in root.iterdir() if p.is_file() and (p.suffix=='.md' or p.name in ['LICENSE','NOTICE'])]+[p for p in (root/'docs').rglob('*') if p.is_file()]
source=[p for p in root.rglob('*') if p.is_file() and not any(part in ['dist','.git','__pycache__'] for part in p.relative_to(root).parts) and p.name!='.DS_Store']
for name,paths in [(f'content-studio-{version}.zip',install),(f'content-studio-source-{version}.zip',source)]:
 with ZipFile(dist/name,'w',ZIP_DEFLATED) as archive:
  for p in sorted(paths):archive.write(p,p.relative_to(root).as_posix())
lines=[]
for p in sorted(dist.glob('*.zip')):lines.append(hashlib.sha256(p.read_bytes()).hexdigest()+'  '+p.name)
(dist/'SHA256SUMS').write_text('\n'.join(lines)+'\n')
print('\n'.join(p.name for p in sorted(dist.iterdir())))
