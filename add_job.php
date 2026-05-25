<?php
require_once '_auth.php';
include 'db.php';

$errors = [];

$job_titles = [
    'Store Operations' => [
        'icon'  => '🏪',
        'color' => '#FF6600',
        'items' => ['Store Supervisor','Assistant Branch Manager','Cashier','Bagger','Promodiser','Merchandiser'],
    ],
    'Warehouse & Logistics' => [
        'icon'  => '📦',
        'color' => '#3A6BC8',
        'items' => ['Warehouse Supervisor','Warehouse Team Leader','Outbound Checker','Delivery Helper','Driver','Production Staff'],
    ],
    'Office & Support' => [
        'icon'  => '💼',
        'color' => '#28A745',
        'items' => ['Compliance Staff','HR Vendor Management Lead','Data Encoder','Office Staff','Accounting Assistant'],
    ],
];

$locations = [
    'Antipolo & Rizal' => [
        'icon'  => '📍',
        'color' => '#FF6600',
        'items' => [
            'MR D.I.Y. Robinsons Antipolo',
            'MR D.I.Y. SM Masinag, Antipolo',
            'MR D.I.Y. Sta. Lucia East Grand Mall, Cainta',
            'MR D.I.Y. Puregold Antipolo (Circumferential)',
            'MR D.I.Y. Robinsons Cainta',
            'MR D.I.Y. Ayala Malls Feliz, Pasig',
        ],
    ],
    'Marikina' => [
        'icon'  => '🏙️',
        'color' => '#8B5CF6',
        'items' => [
            'MR D.I.Y. SM City Marikina',
            'MR D.I.Y. Robinsons Metro East, Pasig–Marikina',
            'MR D.I.Y. Puregold Marikina (Lilac)',
            'MR D.I.Y. Riverbanks Center, Marikina',
        ],
    ],
    'Quezon City (Near Antipolo Side)' => [
        'icon'  => '🌆',
        'color' => '#0891B2',
        'items' => [
            'MR D.I.Y. SM City Fairview',
            'MR D.I.Y. Robinsons Novaliches',
            'MR D.I.Y. SM City San Mateo',
            'MR D.I.Y. Starmall Cubao',
            'MR D.I.Y. Ali Mall, Cubao',
        ],
    ],
];

if (isset($_POST['save'])) {
    $title       = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $location    = mysqli_real_escape_string($conn, trim($_POST['location']));
    $salary      = mysqli_real_escape_string($conn, trim($_POST['salary']));
    $status      = in_array($_POST['status'], ['Open', 'Closed']) ? $_POST['status'] : 'Open';

    if (!$title)    $errors[] = 'Job title is required.';
    if (!$location) $errors[] = 'Location is required.';

    if (empty($errors)) {
        mysqli_query($conn,
            "INSERT INTO jobs (title, description, location, salary, status)
             VALUES ('$title', '$description', '$location', '$salary', '$status')"
        );
        header('Location: dashboard.php?success=Job posted successfully');
        exit;
    }
}

$sel_title    = $_POST['title']    ?? '';
$sel_location = $_POST['location'] ?? '';
$sel_status   = $_POST['status']   ?? 'Open';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job — MR D.I.Y Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>


    .form-card { max-width: 700px; }

    /* Every field wrapper gets a subtle lift on focus-within */
    .field-block {
        margin-bottom: 22px;
    }

    .field-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .field-label .lbl-icon {
        width: 20px; height: 20px;
        border-radius: 5px;
        display: flex; align-items: center; justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
    }

    /* Shared "field surface" — used by text inputs, textarea, salary, custom dropdown trigger */
    .field-surface {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: #FAFAFA;
        font-size: 14px;
        font-family: 'Barlow', sans-serif;
        color: var(--charcoal);
        outline: none;
        transition: border-color .18s, box-shadow .18s, background .18s;
        box-sizing: border-box;
    }

    .field-surface:focus,
    .field-surface:focus-within {
        border-color: var(--orange);
        box-shadow: 0 0 0 3px rgba(255,102,0,.10);
        background: #fff;
    }

    textarea.field-surface {
        resize: vertical;
        min-height: 110px;
        line-height: 1.6;
    }

    /* Salary — icon prefix */
    .salary-wrap {
        position: relative;
    }
    .salary-prefix {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 42px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        font-weight: 700;
        color: var(--orange);
        background: #FFF0E6;
        border-radius: 10px 0 0 10px;
        border-right: 1.5px solid var(--border);
        pointer-events: none;
    }
    .salary-input {
        padding-left: 54px !important;
    }

    /* ── CUSTOM DROPDOWN ──────────────────────── */
    .cs-wrap { position: relative; user-select: none; }

    .cs-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        cursor: pointer;
    }

    .cs-trigger.open {
        border-color: var(--orange) !important;
        box-shadow: 0 0 0 3px rgba(255,102,0,.10) !important;
        background: #fff !important;
        border-radius: 10px 10px 0 0 !important;
    }

    .cs-display { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }

    .cs-placeholder { color: #9CA3AF; font-size: 14px; }

    .cs-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
    }

    .cs-chevron {
        flex-shrink: 0;
        transition: transform .22s ease;
        color: #9CA3AF;
    }
    .cs-trigger.open .cs-chevron { transform: rotate(180deg); color: var(--orange); }

    /* Panel */
    .cs-panel {
        position: absolute;
        top: calc(100% - 2px);
        left: 0; right: 0;
        background: white;
        border: 1.5px solid var(--orange);
        border-top: none;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 16px 40px rgba(0,0,0,.14);
        z-index: 300;
        display: none;
        flex-direction: column;
        max-height: 340px;
        overflow: hidden;
    }
    .cs-panel.open { display: flex; }

    /* Search */
    .cs-search-wrap {
        padding: 10px 12px 8px;
        border-bottom: 1px solid var(--border);
        flex-shrink: 0;
        background: #FAFAFA;
    }
    .cs-search {
        width: 100%;
        padding: 8px 10px 8px 34px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        font-family: 'Barlow', sans-serif;
        outline: none;
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%236B6B6B' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat 10px center;
        transition: border-color .15s;
    }
    .cs-search:focus { border-color: var(--orange); }

    /* List */
    .cs-list { overflow-y: auto; flex: 1; padding: 6px 0 8px; }

    /* Group header */
    .cs-group {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
        border-top: 1px solid #F0F0F0;
        margin-top: 4px;
        background: #FAFAFA;
    }
    .cs-group:first-child { border-top: none; margin-top: 0; }

    .cs-group-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 9px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 800;
    }

    /* Option */
    .cs-option {
        display: flex;
        align-items: center;
        padding: 10px 14px 10px 24px;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--dark);
        cursor: pointer;
        transition: background .10s;
        gap: 8px;
    }
    .cs-option:hover    { background: #FFF5EE; color: var(--orange-dark); }
    .cs-option.selected { background: #FFF0E6; color: var(--orange); font-weight: 700; }
    .cs-option.hidden   { display: none; }

    .cs-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        background: var(--border);
        flex-shrink: 0;
        transition: background .15s;
    }
    .cs-option:hover .cs-dot    { background: var(--orange-light); }
    .cs-option.selected .cs-dot { background: var(--orange); }

    .cs-check {
        margin-left: auto;
        flex-shrink: 0;
        opacity: 0;
        color: var(--orange);
        transition: opacity .15s;
    }
    .cs-option.selected .cs-check { opacity: 1; }

    .cs-empty {
        padding: 20px;
        text-align: center;
        font-size: 13px;
        color: var(--muted);
        display: none;
    }

    /* ── STATUS CARDS ─────────────────────────── */
    .status-cards {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .status-card {
        padding: 14px 16px;
        border-radius: 10px;
        border: 2px solid var(--border);
        cursor: pointer;
        background: #FAFAFA;
        transition: all .18s;
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Barlow', sans-serif;
    }

    .status-card:hover { border-color: #ccc; background: white; }

    .status-card-icon {
        width: 38px; height: 38px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        background: var(--border);
        transition: background .18s;
    }

    .status-card-text { flex: 1; }
    .status-card-title { font-size: 14px; font-weight: 700; color: var(--charcoal); line-height: 1; }
    .status-card-sub   { font-size: 11px; color: var(--muted); margin-top: 3px; }

    .status-card-radio {
        width: 18px; height: 18px;
        border-radius: 50%;
        border: 2px solid var(--border);
        flex-shrink: 0;
        transition: all .18s;
        display: flex; align-items: center; justify-content: center;
    }

    /* Open state */
    .status-card.is-open {
        border-color: var(--success);
        background: #F0FFF4;
    }
    .status-card.is-open .status-card-icon { background: #D4EDDA; }
    .status-card.is-open .status-card-title { color: var(--success); }
    .status-card.is-open .status-card-radio {
        border-color: var(--success);
        background: var(--success);
        box-shadow: 0 0 0 3px rgba(40,167,69,.15);
    }
    .status-card.is-open .status-card-radio::after {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: white;
    }

    /* Closed state */
    .status-card.is-closed {
        border-color: #9CA3AF;
        background: #F7F7F7;
    }
    .status-card.is-closed .status-card-icon { background: #E8E8E8; }
    .status-card.is-closed .status-card-title { color: var(--mid); }
    .status-card.is-closed .status-card-radio {
        border-color: #9CA3AF;
        background: #9CA3AF;
        box-shadow: 0 0 0 3px rgba(107,107,107,.15);
    }
    .status-card.is-closed .status-card-radio::after {
        content: '';
        width: 6px; height: 6px;
        border-radius: 50%;
        background: white;
    }

    /* ── FORM ACTIONS ─────────────────────────── */
    .form-actions {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 12px;
    }
    </style>
</head>
<body>

<?php include '_sidebar.php'; ?>

<div class="main">

    <div class="page-header">
        <div>
            <div class="page-title">Post a New Job</div>
            <div class="page-subtitle">Fill in the details below to create a new listing</div>
        </div>
        <a href="dashboard.php" class="btn btn-ghost">&larr; Back to Dashboard</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom:20px;max-width:700px;">
            <?php foreach ($errors as $e): ?>&#9888; <?= htmlspecialchars($e) ?><br><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" id="jobForm">

            <!-- Hidden real inputs -->
            <input type="hidden" name="title"    id="hTitle"    value="<?= htmlspecialchars($sel_title) ?>">
            <input type="hidden" name="location" id="hLocation" value="<?= htmlspecialchars($sel_location) ?>">
            <input type="hidden" name="status"   id="hStatus"   value="<?= htmlspecialchars($sel_status) ?>">

            <!-- ── Job Title ─────────────────────────── -->
            <div class="field-block">
                <div class="field-label">
                    <span class="lbl-icon" style="background:#FFF0E6;">👔</span>
                    Job Title <span style="color:var(--orange);margin-left:2px;">*</span>
                </div>
                <div class="cs-wrap" id="wrapTitle">
                    <div class="cs-trigger field-surface" id="trigTitle" onclick="toggleDrop('title')">
                        <div class="cs-display">
                            <span id="dispTitle">
                                <?php if ($sel_title): ?>
                                    <?php foreach ($job_titles as $g => $gd): if (in_array($sel_title, $gd['items'])): ?>
                                        <span class="cs-badge" style="background:<?= $gd['color'] ?>18;color:<?= $gd['color'] ?>"><?= $gd['icon'] ?> <?= htmlspecialchars($sel_title) ?></span>
                                    <?php endif; endforeach; ?>
                                <?php else: ?>
                                    <span class="cs-placeholder">Select a position…</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <svg class="cs-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="cs-panel" id="panelTitle">
                        <div class="cs-search-wrap">
                            <input type="text" class="cs-search" id="searchTitle" placeholder="Search positions…" oninput="filterDrop('title',this.value)">
                        </div>
                        <div class="cs-list" id="listTitle">
                            <?php foreach ($job_titles as $group => $gd): ?>
                                <div class="cs-group" data-group="t-<?= md5($group) ?>">
                                    <span class="cs-group-pill" style="background:<?= $gd['color'] ?>18;color:<?= $gd['color'] ?>"><?= $gd['icon'] ?> <?= htmlspecialchars($group) ?></span>
                                </div>
                                <?php foreach ($gd['items'] as $t): ?>
                                    <div class="cs-option <?= $sel_title === $t ? 'selected' : '' ?>"
                                         data-value="<?= htmlspecialchars($t) ?>"
                                         data-group="t-<?= md5($group) ?>"
                                         data-icon="<?= $gd['icon'] ?>"
                                         data-color="<?= htmlspecialchars($gd['color']) ?>"
                                         onclick="pickOption('title',this)">
                                        <span class="cs-dot"></span>
                                        <?= htmlspecialchars($t) ?>
                                        <svg class="cs-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            <div class="cs-empty" id="noTitle">No matching positions.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Description ──────────────────────── -->
            <div class="field-block">
                <div class="field-label">
                    <span class="lbl-icon" style="background:#F0F4FF;">📝</span>
                    Description
                </div>
                <textarea name="description" class="field-surface"
                          placeholder="Describe the role, responsibilities, and requirements…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- ── Location + Salary row ─────────────── -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <!-- Location -->
                <div class="field-block">
                    <div class="field-label">
                        <span class="lbl-icon" style="background:#FFF0E6;">📍</span>
                        Branch / Location <span style="color:var(--orange);margin-left:2px;">*</span>
                    </div>
                    <div class="cs-wrap" id="wrapLocation">
                        <div class="cs-trigger field-surface" id="trigLocation" onclick="toggleDrop('location')">
                            <div class="cs-display">
                                <span id="dispLocation">
                                    <?php if ($sel_location): ?>
                                        <?php foreach ($locations as $a => $ld): if (in_array($sel_location, $ld['items'])): ?>
                                            <span class="cs-badge" style="background:<?= $ld['color'] ?>18;color:<?= $ld['color'] ?>"><?= $ld['icon'] ?> <?= htmlspecialchars($sel_location) ?></span>
                                        <?php endif; endforeach; ?>
                                    <?php else: ?>
                                        <span class="cs-placeholder">Select a branch…</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <svg class="cs-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                        </div>
                        <div class="cs-panel" id="panelLocation">
                            <div class="cs-search-wrap">
                                <input type="text" class="cs-search" id="searchLocation" placeholder="Search branches…" oninput="filterDrop('location',this.value)">
                            </div>
                            <div class="cs-list" id="listLocation">
                                <?php foreach ($locations as $area => $ld): ?>
                                    <div class="cs-group" data-group="l-<?= md5($area) ?>">
                                        <span class="cs-group-pill" style="background:<?= $ld['color'] ?>18;color:<?= $ld['color'] ?>"><?= $ld['icon'] ?> <?= htmlspecialchars($area) ?></span>
                                    </div>
                                    <?php foreach ($ld['items'] as $b): ?>
                                        <div class="cs-option <?= $sel_location === $b ? 'selected' : '' ?>"
                                             data-value="<?= htmlspecialchars($b) ?>"
                                             data-group="l-<?= md5($area) ?>"
                                             data-icon="<?= $ld['icon'] ?>"
                                             data-color="<?= htmlspecialchars($ld['color']) ?>"
                                             onclick="pickOption('location',this)">
                                            <span class="cs-dot"></span>
                                            <?= htmlspecialchars($b) ?>
                                            <svg class="cs-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                <div class="cs-empty" id="noLocation">No matching branches.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary -->
                <div class="field-block">
                    <div class="field-label">
                        <span class="lbl-icon" style="background:#FFFBE6;">💰</span>
                        Salary (PHP / day)
                    </div>
                    <div class="salary-wrap">
                        <span class="salary-prefix">₱</span>
                        <input type="text" name="salary" class="field-surface salary-input"
                                placeholder="e.g. 610 – 750"
                                value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>

            </div>

            <!-- ── Status ────────────────────────────── -->
            <div class="field-block">
                <div class="field-label">
                    <span class="lbl-icon" style="background:#F0FFF4;">🔖</span>
                    Status
                </div>
                <div class="status-cards">
                    <div class="status-card <?= $sel_status === 'Open' ? 'is-open' : '' ?>" id="cardOpen" onclick="pickStatus('Open')">
                        <div class="status-card-icon">✅</div>
                        <div class="status-card-text">
                            <div class="status-card-title">Open</div>
                            <div class="status-card-sub">Accepting applications</div>
                        </div>
                        <div class="status-card-radio" id="radioOpen"></div>
                    </div>
                    <div class="status-card <?= $sel_status === 'Closed' ? 'is-closed' : '' ?>" id="cardClosed" onclick="pickStatus('Closed')">
                        <div class="status-card-icon">🔒</div>
                        <div class="status-card-text">
                            <div class="status-card-title">Closed</div>
                            <div class="status-card-sub">No longer accepting</div>
                        </div>
                        <div class="status-card-radio" id="radioClosed"></div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="save" class="btn btn-primary btn-lg" onclick="return validateForm()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Post Job
                </button>
                <a href="dashboard.php" class="btn btn-ghost btn-lg">Cancel</a>
            </div>

        </form>
    </div>

</div>

<script>
const state = { title: false, location: false };

function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function toggleDrop(key) {
    const was = state[key];
    closeAll();
    if (!was) {
        state[key] = true;
        document.getElementById('trig' + cap(key)).classList.add('open');
        document.getElementById('panel' + cap(key)).classList.add('open');
        setTimeout(() => document.getElementById('search' + cap(key))?.focus(), 60);
    }
}

function closeAll() {
    ['title','location'].forEach(k => {
        state[k] = false;
        document.getElementById('trig' + cap(k))?.classList.remove('open');
        document.getElementById('panel' + cap(k))?.classList.remove('open');
    });
}

document.addEventListener('click', e => {
    if (!e.target.closest('.cs-wrap')) closeAll();
});

function pickOption(key, el) {
    const val = el.dataset.value, icon = el.dataset.icon, color = el.dataset.color;
    document.getElementById('h' + cap(key)).value = val;
    document.querySelectorAll('#list' + cap(key) + ' .cs-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('disp' + cap(key)).innerHTML =
        `<span class="cs-badge" style="background:${color}18;color:${color}">${icon} ${val}</span>`;
    closeAll();
}

function filterDrop(key, q) {
    const K = cap(key), v = q.trim().toLowerCase();
    const groups = {};
    let any = false;
    document.querySelectorAll('#list' + K + ' .cs-option').forEach(o => {
        const vis = !v || o.dataset.value.toLowerCase().includes(v);
        o.classList.toggle('hidden', !vis);
        if (vis) { any = true; groups[o.dataset.group] = true; }
    });
    document.querySelectorAll('#list' + K + ' .cs-group').forEach(g => {
        g.style.display = groups[g.dataset.group] ? '' : 'none';
    });
    document.getElementById('no' + K).style.display = any ? 'none' : 'block';
}

function pickStatus(val) {
    document.getElementById('hStatus').value = val;
    document.getElementById('cardOpen').className   = 'status-card' + (val === 'Open'   ? ' is-open'   : '');
    document.getElementById('cardClosed').className = 'status-card' + (val === 'Closed' ? ' is-closed' : '');
}
// init on load
pickStatus('<?= $sel_status ?: 'Open' ?>');

function validateForm() {
    if (!document.getElementById('hTitle').value) {
        alert('Please select a job title.');
        toggleDrop('title');
        return false;
    }
    if (!document.getElementById('hLocation').value) {
        alert('Please select a branch location.');
        toggleDrop('location');
        return false;
    }
    return true;
}
</script>

</body>
</html>
