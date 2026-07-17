<?php

session_start();
include("../../db.php");

if (!isset($_GET['id'])) {
    exit();
}

$complaint_id = intval($_GET['id']);
$admin_id = $_SESSION['admin_id'];

function status_badge_class($s)
{
    $s = strtolower($s);
    if ($s === 'pending') return 'badge-status-pending';
    if ($s === 'in_progress') return 'badge-status-progress';
    if ($s === 'resolved') return 'badge-status-resolved';
    if ($s === 'rejected') return 'badge-status-rejected';
    return 'badge-status-pending';
}

function priority_badge_class($p)
{
    $p = strtolower($p);
    if ($p === 'high') return 'badge-priority-high';
    if ($p === 'medium') return 'badge-priority-medium';
    if ($p === 'low') return 'badge-priority-low';
    if ($p === 'critical') return 'badge-priority-critical';
    return 'badge-priority-medium';
}

$workers = [];

$w_sql = "SELECT worker_id,name,department
          FROM worker
          ORDER BY name";

$w_res = $conn->query($w_sql);

while ($row = $w_res->fetch_assoc()) {
    $workers[] = $row;
}

$c_sql = "SELECT
            c.complaint_id,
            c.category,
            c.description,
            c.severity,
            c.status,
            c.location,
            c.filed_date,
            cz.name AS citizen_name,
            c.location AS citizen_address,
            w.name AS worker_name,
            w.department AS worker_dept
          FROM complaint c
          JOIN citizen cz
            ON c.citizen_id = cz.citizen_id
          LEFT JOIN worker w
            ON c.worker_id = w.worker_id
          WHERE c.complaint_id = ?";

$stmt = $conn->prepare($c_sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();

$c = $stmt->get_result()->fetch_assoc();

if (!$c) {
    exit();
}

?>

<div class="complaint-card">
    <div class="card-header">
        <div>
            <div class="card-title">
                <?php echo htmlspecialchars($c['category']); ?>
            </div>

            <div class="card-meta">
                ID: C<?php echo str_pad($c['complaint_id'], 3, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <div class="badge-row">
            <span class="badge-status <?php echo status_badge_class($c['status']); ?>">
                <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $c['status']))); ?>
            </span>

            <span class="badge-priority <?php echo priority_badge_class($c['severity']); ?>">
                <?php echo htmlspecialchars(ucfirst(strtolower($c['severity']))); ?> Priority
            </span>
        </div>
    </div>

    <div class="card-body">

        <div class="info-row">
            <span class="info-icon">👤</span>
            <span>
                Citizen:
                <?php echo htmlspecialchars($c['citizen_name']); ?>
            </span>
        </div>

        <div class="info-row">
            <span class="info-icon">📍</span>
            <span>
                <?php echo htmlspecialchars($c['citizen_address']); ?>
            </span>
        </div>

        <div class="info-row">
            <span class="info-icon">📅</span>
            <span>
                Filed:
                <?php echo htmlspecialchars(date('Y-m-d', strtotime($c['filed_date']))); ?>
            </span>
        </div>

    </div>

    <div class="card-footer">

        <form method="post" style="width:100%;">

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:10px;">

                <a href="ViewComplaint.php?id=<?php echo $c['complaint_id']; ?>" class="view-btn">
                    View Details
                </a>

                <button type="submit" name="assign_worker" class="assign-btn">
                    Assign
                </button>

            </div>

            <div class="form-fields">

                <input type="hidden"
                       name="complaint_id"
                       value="<?php echo $c['complaint_id']; ?>">

                <select name="worker_id" class="worker-select">

                    <option value="0">Select worker</option>

                    <?php foreach ($workers as $w): ?>

                        <option value="<?php echo $w['worker_id']; ?>">
                            <?php
                            echo htmlspecialchars(
                                $w['name'] .
                                ($w['department'] ? ' - ' . $w['department'] : '')
                            );
                            ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <select name="severity" class="severity-select">

                    <?php

                    $current = strtolower($c['severity']);

                    $opts = [
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical'
                    ];

                    foreach ($opts as $val => $label) {

                        $sel = ($current === $val) ? 'selected' : '';

                        echo "<option value=\"$val\" $sel>$label Severity</option>";

                    }

                    ?>

                </select>

            </div>

        </form>

    </div>

</div>

<?php

$stmt->close();
$conn->close();

?>