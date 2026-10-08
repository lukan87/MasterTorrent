# Torrent row styling backup

These copies preserve the exact files from before the reference-image redesign, including all earlier edits.

Undo this redesign:

```bash
python3 /var/www/fileiplay.org/.backups/torrent-row-style-20261007-194009/undo.py
```

Check without changing files by adding `--check`.
The command stops if any of these files were edited after the redesign, to protect later work.
The `original/` directory also contains copies that can be restored individually.
