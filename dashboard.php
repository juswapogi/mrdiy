<?php
require_once '_auth.php';
include 'db.php';
// Stats
$totalJobs       = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jobs"));
$openJobs        = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jobs WHERE status='Open'"));
$totalApplicants = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='user'"));
$pendingUsers    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='user' AND status='Pending'"));
$approved        = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='user' AND status='Approved'"));
$rejected        = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='user' AND status='Rejected'"));

// Job listings
$jobs = mysqli_query($conn, "SELECT * FROM jobs ORDER BY id DESC");

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — MR D.I.Y Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <div class="page-title">Recruitment Dashboard</div>
            <div class="page-subtitle">Welcome back, <?= htmlspecialchars($adminName) ?> · <?= date('F j, Y') ?></div>
        </div>
        <a href="add_job.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Job
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💼</div>
            <div>
                <div class="stat-value"><?= $totalJobs ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div>
                <div class="stat-value"><?= $openJobs ?></div>
                <div class="stat-label">Open Positions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-value"><?= $totalApplicants ?></div>
                <div class="stat-label">Applicants</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div>
                <div class="stat-value"><?= $pendingUsers ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
        </div>
    </div>

    <!-- Applicant Breakdown -->
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;margin-bottom:24px;">

        <div class="card" style="padding:24px;">
            <div style="font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;margin-bottom:20px;">Applicant Status</div>
            <div style="display:flex;flex-direction:column;gap:14px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:14px;color:var(--muted);font-weight:500;">Approved</span>
                    <span class="badge badge-approved"><?= $approved ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:14px;color:var(--muted);font-weight:500;">Pending</span>
                    <span class="badge badge-pending"><?= $pendingUsers ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:14px;color:var(--muted);font-weight:500;">Rejected</span>
                    <span class="badge badge-rejected"><?= $rejected ?></span>
                </div>
            </div>
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                <a href="applicants.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;">
                    View All Applicants →
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="padding:24px;">
            <div style="font-family:'Barlow Condensed',sans-serif;font-size:18px;font-weight:700;margin-bottom:20px;">Quick Actions</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <a href="add_job.php" class="btn btn-primary" style="justify-content:center;padding:14px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Post New Job
                </a>
                <a href="applicants.php" class="btn btn-secondary" style="justify-content:center;padding:14px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    Review Applicants
                </a>
            </div>
        </div>

    </div>

    <!-- Job Listings -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Job Listings</div>
            <a href="add_job.php" class="btn btn-ghost btn-sm">+ Add New</a>
        </div>

        <div class="card-body">
            <?php if (mysqli_num_rows($jobs) === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-title">No jobs posted yet</div>
                    <div class="empty-state-text">Start by adding your first job listing.</div>
                    <a href="add_job.php" class="btn btn-primary">Post a Job</a>
                </div>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($jobs)): ?>
                <div class="job-row">
                    <div class="job-info">
                        <div class="job-title"><?= htmlspecialchars($row['title']) ?></div>
                        <div class="job-meta">
                            📍 <?= htmlspecialchars($row['location']) ?>
                            &nbsp;·&nbsp;
                            💰 ₱<?= htmlspecialchars($row['salary']) ?>
                        </div>
                    </div>

                    <div class="job-badges">
                        <span class="badge badge-orange">Full-time</span>
                        <?php if (strtolower($row['status']) === 'open'): ?>
                            <span class="badge badge-open">● Open</span>
                        <?php else: ?>
                            <span class="badge badge-closed">● Closed</span>
                        <?php endif; ?>
                    </div>

                    <div class="job-actions">
                        <a href="edit_job.php?id=<?= $row['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                        <button
                            class="btn btn-danger btn-sm"
                            onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>')">
                            Delete
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">🗑️</div>
        <div class="modal-title">Delete Job?</div>
        <p class="modal-text" id="modalText">This will permanently remove the job listing.</p>
        <div class="modal-actions">
            <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
            <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Yes, Delete</a>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="toast" id="toast">
    ✅ <?= htmlspecialchars($_GET['success']) ?>
</div>
<script>setTimeout(() => document.getElementById('toast').remove(), 3500);</script>
<?php endif; ?>

<script>
function confirmDelete(id, title) {
    document.getElementById('modalText').textContent = `Delete "${title}"? This cannot be undone.`;
    document.getElementById('confirmDeleteBtn').href = `delete_job.php?id=${id}`;
    document.getElementById('deleteModal').classList.add('active');
}
function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
