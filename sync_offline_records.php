<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Please log in again.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$records = isset($input['records']) && is_array($input['records'])
    ? $input['records']
    : [];

if (!$records) {
    echo json_encode([
        'success' => true,
        'synced' => [],
        'failed' => [],
        'message' => 'Nothing to synchronize.'
    ]);
    exit;
}

$checkLog = $conn->prepare(
    "SELECT record_id FROM offline_sync_log WHERE user_id = ? AND client_id = ? LIMIT 1"
);

$insertLog = $conn->prepare(
    "INSERT INTO offline_sync_log (user_id, client_id, record_id, synced_at)
     VALUES (?, ?, ?, NOW())"
);

$insertRecord = $conn->prepare(
    "INSERT INTO symptoms_records
    (
        user_id, mode, input_type, symptoms, blood_pressure,
        systolic_bp, diastolic_bp, proteinuria,
        gestational_age_weeks, maternal_age_yrs, diabetes,
        previous_pe, multiple_pregnancy, hypertension,
        risk, risk_level, engine_used, message
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$checkLog || !$insertLog || !$insertRecord) {
    echo json_encode([
        'success' => false,
        'error' => 'Synchronization service could not prepare the database statements.'
    ]);
    exit;
}

$synced = [];
$failed = [];

foreach ($records as $record) {
    $client_id = isset($record['client_id']) ? trim((string)$record['client_id']) : '';
    $p = isset($record['payload']) && is_array($record['payload'])
        ? $record['payload']
        : [];

    if ($client_id === '' || !$p) {
        $failed[] = ['client_id' => $client_id, 'error' => 'Invalid offline record.'];
        continue;
    }

    // Idempotency: if this client record was already synchronized,
    // return the existing server ID rather than creating a duplicate.
    $checkLog->bind_param('is', $user_id, $client_id);
    $checkLog->execute();
    $existing = $checkLog->get_result()->fetch_assoc();

    if ($existing) {
        $synced[] = [
            'client_id' => $client_id,
            'record_id' => (int)$existing['record_id'],
            'duplicate' => true
        ];
        continue;
    }

    $mode = isset($p['mode']) ? (string)$p['mode'] : 'home';
    $input_type = isset($p['input_type']) ? (string)$p['input_type'] : 'checkbox';

    $symptoms = $p['symptoms'] ?? '';
    if (is_array($symptoms)) {
        $symptoms = implode(', ', $symptoms);
    }
    $symptoms = trim((string)$symptoms);

    $systolic = (int)($p['systolic_bp'] ?? 0);
    $diastolic = (int)($p['diastolic_bp'] ?? 0);
    $blood_pressure = $systolic > 0 && $diastolic > 0
        ? $systolic . '/' . $diastolic
        : (string)($p['blood_pressure'] ?? '');

    $proteinuria = (string)($p['proteinuria'] ?? 'None');
    $gest = (float)($p['gestational_age_weeks'] ?? 0);
    $age = (int)($p['maternal_age_yrs'] ?? 0);
    $diabetes = (int)($p['diabetes'] ?? 0);
    $previous_pe = (int)($p['previous_pe'] ?? 0);
    $multiple = (int)($p['multiple_pregnancy'] ?? 0);
    $hypertension = (int)($p['hypertension'] ?? 0);
    $risk = max(0, min(100, (int)($p['risk'] ?? 0)));
    $risk_level = (string)($p['risk_level'] ?? 'Low');
    $engine_used = 'Offline Rule-Based';
    $message = (string)($p['message'] ?? 'Saved offline and synchronized later.');

    if ($symptoms === '') {
        $failed[] = ['client_id' => $client_id, 'error' => 'Symptoms are required.'];
        continue;
    }

    $insertRecord->bind_param(
        'issssiisdiiiiiisss',
        $user_id,
        $mode,
        $input_type,
        $symptoms,
        $blood_pressure,
        $systolic,
        $diastolic,
        $proteinuria,
        $gest,
        $age,
        $diabetes,
        $previous_pe,
        $multiple,
        $hypertension,
        $risk,
        $risk_level,
        $engine_used,
        $message
    );

    if (!$insertRecord->execute()) {
        $failed[] = [
            'client_id' => $client_id,
            'error' => 'Database insert failed.'
        ];
        continue;
    }

    $server_id = (int)$conn->insert_id;

    $insertLog->bind_param('isi', $user_id, $client_id, $server_id);
    if (!$insertLog->execute()) {
        $rollbackDelete = $conn->prepare(
            "DELETE FROM symptoms_records WHERE id = ? AND user_id = ? LIMIT 1"
        );

        if ($rollbackDelete) {
            $rollbackDelete->bind_param('ii', $server_id, $user_id);
            $rollbackDelete->execute();
            $rollbackDelete->close();
        }

        $failed[] = [
            'client_id' => $client_id,
            'error' => 'Could not finalize synchronization.'
        ];
        continue;
    }

    $synced[] = [
        'client_id' => $client_id,
        'record_id' => $server_id,
        'duplicate' => false
    ];
}

$checkLog->close();
$insertLog->close();
$insertRecord->close();
$conn->close();

echo json_encode([
    'success' => count($failed) === 0,
    'synced' => $synced,
    'failed' => $failed,
    'message' => count($failed) === 0
        ? 'Synchronization completed.'
        : 'Some records could not be synchronized.'
], JSON_UNESCAPED_UNICODE);
?>