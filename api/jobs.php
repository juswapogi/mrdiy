<?php
/**
 * GET /api/jobs.php
 *
 * Query params:
 *   status  = Open | Closed | All   (default: Open)
 *   page    = 1, 2, 3 …             (default: 1)
 *   limit   = 1–50                  (default: 20)
 *
 * Response: { "success": true, "total": N, "page": 1, "limit": 20, "jobs": [...] }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');          // Allow Android to reach this API
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../db.php';

// ── Input sanitisation ────────────────────────────────────────────────────────
$status = $_GET['status'] ?? 'Open';
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(50, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$allowedStatuses = ['Open', 'Closed', 'All'];
if (!in_array($status, $allowedStatuses)) $status = 'Open';

// ── Build query ───────────────────────────────────────────────────────────────
if ($status === 'All') {
    $where = '1=1';
} else {
    $statusEsc = mysqli_real_escape_string($conn, $status);
    $where = "status = '$statusEsc'";
}

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
    $jobs[] = formatJob($row);
}

echo json_encode([
    'success' => true,
    'total'   => $total,
    'page'    => $page,
    'limit'   => $limit,
    'jobs'    => $jobs,
]);

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatJob(array $row): array {
    return [
        'id'          => (string)$row['id'],
        'title'       => $row['title'] ?? '',
        'description' => $row['description'] ?? '',
        'location'    => $row['location'] ?? '',
        'salary'      => $row['salary'] ?? '',
        'status'      => $row['status'] ?? 'Open',
        'created_at'  => $row['created_at'] ?? '',
        'posted_ago'  => postedAgo($row['created_at'] ?? ''),
    ];
}

/**
 * Convert a MySQL datetime string to a human-readable "X ago" label.
 * Falls back to "Recently" when no timestamp is available.
 */
function postedAgo(?string $datetime): string {
    if (!$datetime) return 'Recently';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)         return 'Just now';
    if ($diff < 3600)       return (int)($diff / 60)   . 'm ago';
    if ($diff < 86400)      return (int)($diff / 3600)  . 'h ago';
    if ($diff < 604800)     return (int)($diff / 86400) . 'd ago';
    if ($diff < 2592000)    return (int)($diff / 604800). 'w ago';
    return (int)($diff / 2592000) . 'mo ago';
}