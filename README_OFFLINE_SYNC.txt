MOTHERCARE OFFLINE + DELETE IMPLEMENTATION

Files:
1. dashboard.html
2. screen6.html
3. delete_symptom_record.php
4. sync_offline_records.php
5. current_user.php
6. service-worker.js
7. mothercare_sync_migration.sql

IMPORTANT:
Run mothercare_sync_migration.sql once in the SAME MotherCare database used by db_connect.php.

What this implements:
- Patient can delete only their own symptoms_records.
- Deletion is permanent in MySQL.
- Offline symptom assessments are saved in IndexedDB.
- Offline assessments use the existing MotherCare PHP fallback scoring rules for continuity.
- When Internet returns, pending records are sent to sync_offline_records.php.
- A unique user/client ID prevents the same offline record from being inserted twice.
- Dashboard Sync Now triggers synchronization.
- Synchronization also starts automatically when Internet returns.
- Service worker caches dashboard.html and screen6.html for offline navigation.

DO NOT replace post_symptom_data.php or VisitSummary.php with these files.
The existing online assessment and dashboard API remain in place.
