<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid job ID']);
    exit;
}

$result = supabaseRequest('jobs?id=eq.' . $id);

if (!$result || count($result) === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Job not found']);
    exit;
}

$row = $result[0];

echo json_encode([
    'success' => true,
    'job' => [
        'id' => (string)$row['id'],
        'title' => $row['title'] ?? '',
        'description' => $row['description'] ?? '',
        'location' => $row['location'] ?? '',
        'salary' => $row['salary'] ?? '',
        'status' => $row['status'] ?? 'Open',
        'created_at' => $row['created_at'] ?? '',
        'posted_ago' => postedAgo($row['created_at'] ?? '')
    ]
]);

function postedAgo(?string $datetime): string {
    if (!$datetime) return 'Recently';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return (int)($diff / 60) . 'm ago';
    if ($diff < 86400) return (int)($diff / 3600) . 'h ago';
    if ($diff < 604800) return (int)($diff / 86400) . 'd ago';
    if ($diff < 2592000) return (int)($diff / 604800) . 'w ago';
    return (int)($diff / 2592000) . 'mo ago';
}
?>
