#!/bin/python3
# Configuration
import os
import time
from datetime import datetime, timedelta

# Configuration
TARGET_DIRECTORY = "/localdisk/home/s2761220/public_html/" 
TARGET_EXTENSIONS = {'.zip', '.fasta', '.tsv', '.txt', '.png'}
PROTECTED_STRING = 'ae16a9801cdd61a0'  # Case-sensitive match
DAYS_OLD = 7

def cleanup_old_files():
    cutoff_time = time.time() - (DAYS_OLD * 24 * 60 * 60)

    deleted_files = []
    protected_files = []
    error_files = []

    for entry in os.scandir(TARGET_DIRECTORY):
        try:
            if entry.is_file():
                # Get file properties
                ext = os.path.splitext(entry.name)[1].lower()
                filename = entry.name

                # Skip protected files
                if PROTECTED_STRING in filename:
                    protected_files.append(entry.path)
                    continue

                # Check extension and age
                if ext in TARGET_EXTENSIONS and entry.stat().st_mtime < cutoff_time:
                    os.remove(entry.path)
                    deleted_files.append(entry.path)

        except Exception as e:
            error_files.append((entry.path, str(e)))
            continue

    # Generate report
    print(f"Cleanup Report ({datetime.now().isoformat()})")
    print(f"\nProtected {len(protected_files)} files:")
    for f in protected_files:
        print(f" - {os.path.basename(f)}")

    print(f"\nDeleted {len(deleted_files)} files:")
    for f in deleted_files:
        print(f" - {os.path.basename(f)}")

    if error_files:
        print(f"\nErrors with {len(error_files)} files:")
        for f, e in error_files:
            print(f" - {os.path.basename(f)}: {e}")

if __name__ == "__main__":
    cleanup_old_files()
