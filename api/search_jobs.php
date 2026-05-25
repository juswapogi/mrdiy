<?php
/**
 * GET /api/search_jobs.php
 *
 * Query params:
 *   q        = keyword (searches title + description)
 *   location = location string (partial match)
 *   status   = Open | Closed | All   (default: Open)
 *   page     = 1, 2 …                (default: 1)
 *   limit    = 1–50                  (default: 20)
 *
 * Response: { "success": true, "query": "...", "total": N, "jobs": [...] }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../db.php';

// ── Inputs ────────────────────────────────────────────────────────────────────
$q        = trim($_GET['q']        ?? '');
$location = trim($_GET['location'] ?? '');
$status   = $_GET['status']        ?? 'Open';
$page     = max(1, (int)($_GET['page']  ?? 1));
$limit    = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$offset   = ($page - 1) * $limit;

$allowedStatuses = ['Open', 'Closed', 'All'];
if (!in_array($status, $allowedStatuses)) $status = 'Open';

// ── Build WHERE clauses ───────────────────────────────────────────────────────
$conditions = [];

if ($status !== 'All') {
    $statusEsc    = mysqli_real_escape_string($conn, $status);
    $conditions[] = "status = '$statusEsc'";
}

if ($q !== '') {
    $qEsc         = mysqli_real_escape_string($conn, $q);
    $conditions[] = "(title LIKE '%$qEsc%' OR description LIKE '%$qEsc%')";
}

if ($location !== '') {
    $locEsc       = mysqli_real_escape_string($conn, $location);
    $conditions[] = "location LIKE '%$locEsc%'";
}

$where = empty($conditions) ? '1=1' : implode(' AND ', $conditions);

// ── Execute ───────────────────────────────────────────────────────────────────
$totalResult = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM jobs WHERE $where");
$total       = (int)mysqli_fetch_assoc($totalResult)['cnt'];

$result = mysqli_query($conn,
    "SELECT id, title, description, location, salary, status, created_at
     FROM jobs
     WHERE $where
     ORDER BY id DESC
     LIMIT $limit OFFSET $offset"
);

$jobs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $jobs[] = [
        'id'          => (string)$row['id'],
        'title'       => $row['title']       ?? '',
        'description' => $row['description'] ?? '',
        'location'    => $row['location']    ?? '',
        'salary'      => $row['salary']      ?? '',
        'status'      => $row['status']      ?? 'Open',
        'created_at'  => $row['created_at']  ?? '',
        'posted_ago'  => postedAgo($row['created_at'] ?? ''),
    ];
}

echo json_encode([
    'success'  => true,
    'query'    => $q,
    'location' => $location,
    'status'   => $status,
    'total'    => $total,
    'page'     => $page,
    'limit'    => $limit,
    'jobs'     => $jobs,
]);

function postedAgo(?string $datetime): string {
    if (!$datetime) return 'Recently';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)      return 'Just now';
    if ($diff < 3600)    return (int)($diff / 60)    . 'm ago';
    if ($diff < 86400)   return (int)($diff / 3600)  . 'h ago';
    if ($diff < 604800)  return (int)($diff / 86400) . 'd ago';
    if ($diff < 2592000) return (int)($diff / 604800). 'w ago';
    return (int)($diff / 2592000) . 'mo ago';
}