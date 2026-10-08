#!/usr/bin/env python3
"""Restore only the files changed by this torrent row redesign."""
from pathlib import Path
import hashlib
import json
import sys

backup = Path(__file__).resolve().parent
root = backup.parents[1]
manifest = json.loads((backup / 'manifest.json').read_text())
changed = []
for name, info in manifest.items():
    target = root / name
    current = hashlib.sha256(target.read_bytes()).hexdigest() if target.exists() else None
    if current != info['after_sha256']:
        changed.append(name)
if changed:
    sys.exit('Undo stopped: these files changed after the redesign. Preserve those edits before restoring:\n' + '\n'.join(changed))
if '--check' in sys.argv:
    print('Undo ready: all redesign files match the saved version.')
    sys.exit(0)
for name, info in manifest.items():
    target = root / name
    if info['existed']:
        target.write_bytes((backup / 'original' / name).read_bytes())
    elif target.exists():
        target.unlink()
print('Torrent row redesign undone. Earlier edits have been restored.')
