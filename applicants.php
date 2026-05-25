<?php
require_once '_auth.php';
include 'db.php';
// Filter by status
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['Pending','Approved','Rejected'])
    ? $_GET['filter'] : '';

$search = isset($_GET['search']) ? db_real_escape_string($conn, trim($_GET['search'])) : '';

$where = "WHERE role='user'";
if ($filter) $where .= " AND status='$filter'";
if ($search) $where .= " AND (fullname LIKE '%$search%' OR email LIKE '%$search%')";

$users = db_query($conn, "SELECT * FROM users $where ORDER BY id DESC");

$counts = [
    'all'      => db_num_rows(db_query($conn, "SELECT id FROM users WHERE role='user'")),
    'Pending'  => db_num_rows(db_query($conn, "SELECT id FROM users WHERE role='user' AND status='Pending'")),
    'Approved' => db_num_rows(db_query($conn, "SELECT id FROM users WHERE role='user' AND status='Approved'")),
    'Rejected' => db_num_rows(db_query($conn, "SELECT id FROM users WHERE role='user' AND status='Rejected'")),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants — MR D.I.Y Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 7px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid var(--border);
            color: var(--muted);
            background: white;
            transition: all 0.15s;
        }
        .filter-tab:hover { border-color: var(--orange); color: var(--orange); }
        .filter-tab.active { background: var(--orange); color: white; border-color: var(--orange); }
        .filter-tab .count {
            background: rgba(255,255,255,0.25);
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 11px;
            margin-left: 4px;
        }
        .filter-tab:not(.active) .count { background: var(--bg); color: var(--muted); }
    </style>
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main">

    <div class="page-header">
        <div>
            <div class="page-title">Applicants</div>
            <div class="page-subtitle">Review and manage applicant registrations</div>
        </div>

        <!-- Search -->
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <?php if ($filter): ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
            <div class="search-bar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" class="form-control" placeholder="Search name or email…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            <?php if ($search): ?><a href="applicants.php<?= $filter ? '?filter='.$filter : '' ?>" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
        </form>
    </div>

    <!-- Status Filter Tabs -->
    <div class="filter-tabs">
        <a href="applicants.php" class="filter-tab <?= !$filter ? 'active' : '' ?>">
            All <span class="count"><?= $counts['all'] ?></span>
        </a>
        <a href="applicants.php?filter=Pending" class="filter-tab <?= $filter === 'Pending' ? 'active' : '' ?>">
            ⏳ Pending <span class="count"><?= $counts['Pending'] ?></span>
        </a>
        <a href="applicants.php?filter=Approved" class="filter-tab <?= $filter === 'Approved' ? 'active' : '' ?>">
            ✅ Approved <span class="count"><?= $counts['Approved'] ?></span>
        </a>
        <a href="applicants.php?filter=Rejected" class="filter-tab <?= $filter === 'Rejected' ? 'active' : '' ?>">
            ❌ Rejected <span class="count"><?= $counts['Rejected'] ?></span>
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <?= $filter ?: 'All' ?> Applicants
            </div>
            <div style="font-size:13px;color:var(--muted);"><?= db_num_rows($users) ?> result(s)</div>
        </div>

        <div class="card-body">
            <?php if (db_num_rows($users) === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👤</div>
                    <div class="empty-state-title">No applicants found</div>
                    <div class="empty-state-text">
                        <?= $search ? 'No results match your search.' : 'No applicants in this category yet.' ?>
                    </div>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; while ($row = db_fetch_assoc($users)): ?>
                        <tr>
                            <td style="color:var(--muted);font-size:13px;"><?= $i++ ?></td>
                            <td>
                                <div class="td-name"><?= htmlspecialchars($row['fullname']) ?></div>
                            </td>
                            <td style="color:var(--muted);font-size:13px;"><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php $s = $row['status']; ?>
                                <span class="badge badge-<?= strtolower($s) ?>">
                                    <?= $s === 'Approved' ? '✅' : ($s === 'Rejected' ? '❌' : '⏳') ?>
                                    <?= htmlspecialchars($s) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status'] !== 'Approved'): ?>
                                    <a href="approve.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                <?php endif; ?>
                                <?php if ($row['status'] !== 'Rejected'): ?>
                                    <a href="reject.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                                <?php endif; ?>
                                <?php if ($row['status'] !== 'Pending'): ?>
                                    <a href="reset_applicant.php?id=<?= $row['id'] ?>" class="btn btn-ghost btn-sm">Reset</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php if (isset($_GET['success'])): ?>
<div class="toast" id="toast">✅ <?= htmlspecialchars($_GET['success']) ?></div>
<script>setTimeout(() => document.getElementById('toast').remove(), 3500);</script>
<?php endif; ?>

</body>
</html>
