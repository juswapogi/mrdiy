<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../db.php';

$q = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$status = $_GET['status'] ?? 'Open';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$params = 'select=*&order=id.desc&limit=' . $limit . '&offset=' . $offset;

if ($status !== 'All') {
    $params .= '&status=eq.' . urlencode($status);
}

if ($q !== '') {
    $params .= '&or=(title.ilike.' . urlencode('%' . $q . '%') . ',description.ilike.' . urlencode('%' . $q . '%') . ')';
}

if ($location !== '') {
    $params .= '&location=ilike.' . urlencode('%' . $location . '%');
}

$result = supabaseRequest('jobs?' . $params);

$totalResult = supabaseRequest('jobs?select=id');
$total = is_array($totalResult) ? count($totalResult) : 0;

$jobs = [];
if (is_array($result)) {
    foreach ($result as $row) {
        $jobs[] = [
            'id' => (string)$row['id'],
            'title' => $row['title'] ?? '',
            'description' => $row['description'] ?? '',
            'location' => $row['location'] ?? '',
            'salary' => $row['salary'] ?? '',
            'status' => $row['status'] ?? 'Open',
            'created_at' => $row['created_at'] ?? '',
            'posted_ago' => postedAgo($row['created_at'] ?? '')
        ];
    }
}

echo json_encode([
    'success' => true,
    'query' => $q,
    'location' => $location,
    'status' => $status,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'jobs' => $jobs
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
