<?php
// api/upload_file.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$reqId = $_POST['req_id'] ?? '';
$docType = $_POST['doc_type'] ?? '';
$file = $_FILES['file'] ?? null;

if (!$reqId || !$docType || !$file) {
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$dataFile = __DIR__ . '/../data/requests.json';
$requests = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

if (!isset($requests[$reqId])) {
    echo json_encode(['error' => 'Request not found']);
    exit;
}

// Create Upload Dir
$uploadDir = __DIR__ . '/../uploads/' . $reqId . '/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Sanitize filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeDocName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $docType);
$filename = $safeDocName . '_' . time() . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Update Record
    $uploadRecord = [
        'doc_type' => $docType,
        'filename' => $filename,
        'uploaded_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to uploads array
    if (!isset($requests[$reqId]['uploads'])) {
        $requests[$reqId]['uploads'] = [];
    }
    
    // Remove old upload of same type if exists? Or keep history? let's append.
    $requests[$reqId]['uploads'][] = $uploadRecord;

    // Check completion
    // If all requested docs have at least one upload
    $needed = $requests[$reqId]['docs'];
    $uploadedTypes = array_column($requests[$reqId]['uploads'], 'doc_type');
    
    // Check if every needed doc is in uploadedTypes
    $allDone = true;
    foreach ($needed as $n) {
        if (!in_array($n, $uploadedTypes)) {
            $allDone = false;
            break;
        }
    }
    
    if ($allDone) {
        $requests[$reqId]['status'] = 'completed';
    }

    file_put_contents($dataFile, json_encode($requests, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to move uploaded file']);
}
