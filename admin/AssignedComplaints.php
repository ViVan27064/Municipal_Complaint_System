<?php
session_start();
include '../db.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../LoginAndSignup/login.html");
    exit();
}
$admin_id = $_SESSION['admin_id'];
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reassign_worker'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $worker_id = intval($_POST['worker_id']);
    if ($worker_id > 0) {
        $u_sql = "UPDATE complaint SET worker_id=?,admin_id=?,status='in_progress' WHERE complaint_id=?";
        $u_stmt = $conn->prepare($u_sql);
        $u_stmt->bind_param("iii", $worker_id, $admin_id, $complaint_id);
        $u_stmt->execute();
        $u_stmt->close();
    }
    header("Location: AssignedComplaints.php");
    exit();
}
$notif_sql = "SELECT COUNT(*) AS unread FROM admin_notification WHERE admin_id=? AND status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $admin_id);
$notif_stmt->execute();
$notification_count = $notif_stmt->get_result()->fetch_assoc()['unread'] ?? 0;
$notif_stmt->close();
$workers = [];
$w_sql = "SELECT worker_id,name,department FROM worker ORDER BY name";
$w_res = $conn->query($w_sql);
while ($row = $w_res->fetch_assoc()) {
    $workers[] = $row;
}
$c_sql = "SELECT c.complaint_id,c.category,c.description,c.severity,c.status,c.location,c.filed_date,cz.name AS citizen_name,c.location AS citizen_address,w.name AS worker_name,w.department AS worker_dept FROM complaint c JOIN citizen cz ON c.citizen_id=cz.citizen_id LEFT JOIN worker w ON c.worker_id=w.worker_id WHERE c.worker_id IS NOT NULL ORDER BY c.filed_date DESC,c.complaint_id DESC";
$c_res = $conn->query($c_sql);
function status_badge_class($s)
{
    $s = strtolower($s);
    if ($s === 'pending')
        return 'badge-status-pending';
    if ($s === 'in_progress')
        return 'badge-status-progress';
    if ($s === 'resolved')
        return 'badge-status-resolved';
    if ($s === 'rejected')
        return 'badge-status-rejected';
    return 'badge-status-pending';
}
function priority_badge_class($p)
{
    $p = strtolower($p);
    if ($p === 'high')
        return 'badge-priority-high';
    if ($p === 'medium')
        return 'badge-priority-medium';
    if ($p === 'low')
        return 'badge-priority-low';
    if ($p === 'critical')
        return 'badge-priority-critical';
    return 'badge-priority-medium';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Assigned Complaints | MCCCTS Admin</title>
    <style>
        :root {
            --primary-blue: #007bff;
            --primary-blue-soft: #e6f0ff;
            --success-green: #28a745;
            --danger-red: #dc3545;
            --background-light: #f8f9fa;
            --text-dark: #212529;
            --border-color: #dee2e6;
            --card-bg: #ffffff;
            --warning-orange: #ffc107;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
        }

        .header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-icon {
            font-size: 1.3rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            margin-left: 20px;
            padding: 6px 14px;
            display: flex;
            align-items: center;
            font-size: .95rem;
            border-radius: 999px;
            transition: .2s;
        }

        .nav-link:hover {
            background-color: var(--primary-blue-soft);
            color: var(--primary-blue);
        }

        .nav-link.active {
            background-color: var(--primary-blue);
            color: #fff;
        }

        .notifications-badge {
            background-color: var(--danger-red);
            color: #fff;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: .7rem;
            margin-left: 4px;
        }

        .icon {
            display: inline-block;
            margin-right: 6px;
        }

        .icon-doc::before {
            content: '📄';
        }

        .icon-users::before {
            content: '👥';
        }

        .icon-bell::before {
            content: '🔔';
        }

        .icon-profile::before {
            content: '👤';
        }

        .page-container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .complaint-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            padding: 20px 22px;
            margin-bottom: 18px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .03);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .card-meta {
            font-size: .88rem;
            color: #6c757d;
        }

        .badge-row {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .badge-status {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            color: #fff;
        }

        .badge-status-pending {
            background-color: var(--warning-orange);
            color: #000;
        }

        .badge-status-progress {
            background-color: var(--primary-blue);
        }

        .badge-status-resolved {
            background-color: var(--success-green);
        }

        .badge-status-rejected {
            background-color: var(--danger-red);
        }

        .badge-priority {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 500;
            border: 1px solid transparent;
        }

        .badge-priority-high {
            border-color: var(--danger-red);
            color: var(--danger-red);
            background: #ffe6ea;
        }

        .badge-priority-medium {
            border-color: #fd7e14;
            color: #fd7e14;
            background: #fff3e6;
        }

        .badge-priority-low {
            border-color: #17a2b8;
            color: #17a2b8;
            background: #e2f7fb;
        }

        .badge-priority-critical {
            border-color: #6f42c1;
            color: #6f42c1;
            background: #f2e6ff;
        }

        .card-body {
            margin-top: 8px;
            font-size: .92rem;
        }

        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            color: #495057;
        }

        .info-icon {
            margin-right: 6px;
        }

        .assigned-banner {
            margin-top: 10px;
            background: #f1f3f9;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: .9rem;
        }

        .card-footer {
            margin-top: 14px;
            border-top: 1px solid #eef0f4;
            padding-top: 12px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .worker-select {
            flex: 1;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: .9rem;
        }

        .reassign-btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            background-color: var(--primary-blue);
            color: #fff;
            font-size: .9rem;
            font-weight: 500;
            cursor: pointer;
            transition: .2s;
        }

        .reassign-btn:hover {
            background-color: #0056d6;
        }

        .view-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .view-btn:hover {
            background-color: var(--background-light);
        }

        .no-actions {
            font-size: .88rem;
            color: #6c757d;
            padding: 6px 10px;
            border-radius: 8px;
            background: #f5f5f8;
        }

        @media(max-width:768px) {
            .page-container {
                padding: 20px;
            }

            .card-header {
                flex-direction: column;
                gap: 8px;
            }

            .badge-row {
                align-items: flex-start;
                flex-direction: row;
                flex-wrap: wrap;
            }

            .card-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .reassign-btn,
            .view-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <header class="header">
        <a href="home.php" class="logo"><span class="logo-icon"></span><span>MCCCTS - Admin</span></a>
        <nav class="nav-links">
            <a href="AllComplaints.php" class="nav-link"><span class="icon icon-doc"></span>All Complaints</a>
            <a href="AssignedComplaints.php" class="nav-link active"><span class="icon icon-users"></span>Assigned
                Complaints</a>
            <a href="Notifications.php"
                class="nav-link<?php if (basename($_SERVER['PHP_SELF']) === 'Notifications.php')
                    echo ' active'; ?>"><span
                    class="icon icon-bell"></span>Notifications<?php if ($notification_count > 0): ?><span
                        class="notifications-badge"><?php echo htmlspecialchars($notification_count); ?></span><?php endif; ?></a>
            <a href="profile.php" class="nav-link"><span class="icon icon-profile"></span>Profile</a>
        </nav>
    </header>
    <main class="page-container">
        <h1 class="page-title">Assigned Complaints</h1>
        <?php while ($c = $c_res->fetch_assoc()): ?>
            <?php $status = strtolower($c['status']);
            $canReassign = in_array($status, ['pending', 'in_progress']); ?>
            <div class="complaint-card">
                <div class="card-header">
                    <div>
                        <div class="card-title"><?php echo htmlspecialchars($c['category']); ?></div>
                        <div class="card-meta">ID: C<?php echo str_pad($c['complaint_id'], 3, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="badge-row">
                        <span
                            class="badge-status <?php echo status_badge_class($c['status']); ?>"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $c['status']))); ?></span>
                        <span
                            class="badge-priority <?php echo priority_badge_class($c['severity']); ?>"><?php echo htmlspecialchars(ucfirst(strtolower($c['severity']))); ?>
                            Priority</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="info-row"><span class="info-icon">👤</span><span>Citizen:
                            <?php echo htmlspecialchars($c['citizen_name']); ?></span></div>
                    <div class="info-row"><span
                            class="info-icon">📍</span><span><?php echo htmlspecialchars($c['citizen_address']); ?></span>
                    </div>
                    <div class="info-row"><span class="info-icon">📅</span><span>Filed:
                            <?php echo htmlspecialchars(date('Y-m-d', strtotime($c['filed_date']))); ?></span></div>
                    <div class="assigned-banner">Assigned Worker:
                        <strong><?php echo htmlspecialchars($c['worker_name']); ?></strong><?php if ($c['worker_dept'])
                               echo ' - ' . htmlspecialchars($c['worker_dept']); ?>
                    </div>
                </div>
                <div class="card-footer">
                    <?php if ($canReassign): ?>
                        <form method="post" style="display:flex;gap:10px;flex:1;align-items:center;">
                            <input type="hidden" name="complaint_id" value="<?php echo $c['complaint_id']; ?>">

                            <!-- ✅ VIEW BUTTON (ADDED) -->

                            <!-- EXISTING REASSIGN CONTROLS -->
                            <select name="worker_id" class="worker-select">
                                <option value="0">Reassign to...</option>
                                <?php foreach ($workers as $w): ?>
                                    <option value="<?php echo $w['worker_id']; ?>">
                                        <?php echo htmlspecialchars($w['name'] . ($w['department'] ? ' - ' . $w['department'] : '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <a href="ViewComplaint.php?id=<?php echo $c['complaint_id']; ?>" class="view-btn">
                                View Details
                            </a>
                            <button type="submit" name="reassign_worker" class="reassign-btn">
                                Reassign
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="no-actions">No further assignment actions for this complaint.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </main>
</body>

</html>