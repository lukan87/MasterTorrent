from pathlib import Path
import hashlib,json,shutil
root=Path(__file__).resolve().parent
live=root.parent.parent
manifest=json.loads((root/'manifest.json').read_text())
for item in manifest['files']:
    path=live/item['path']
    digest=hashlib.sha256(path.read_bytes()).hexdigest() if path.exists() else None
    if digest not in (item['deployed_sha256'], item['original_sha256']):
        raise SystemExit('Refusing to overwrite later changes: '+item['path'])
for item in manifest['files']:
    path=live/item['path']
    if item['original_sha256'] is None:
        if path.exists(): path.unlink()
    else:
        path.parent.mkdir(parents=True,exist_ok=True)
        shutil.copy2(root/'original'/item['path'],path)
print('Original source files restored. Database column/data retained. Run php artisan view:clear.')
