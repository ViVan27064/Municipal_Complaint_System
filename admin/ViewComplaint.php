<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../LoginAndSignup/login.html");
    exit();
}

$admin_id = $_SESSION['admin_id'];

/* 🔔 Notification count */
$notif_sql = "SELECT COUNT(*) AS unread 
              FROM admin_notification 
              WHERE admin_id=? AND status='unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $admin_id);
$notif_stmt->execute();
$notification_count = $notif_stmt->get_result()->fetch_assoc()['unread'] ?? 0;
$notif_stmt->close();

/* 🔍 Validate complaint ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid complaint ID.");
}

$complaint_id = (int) $_GET['id'];

/* 📄 Fetch complaint details */
$sql = "
    SELECT 
        c.complaint_id,
        c.category,
        c.description,
        c.severity,
        c.status,
        c.location,
        c.filed_date,
        c.resolved_date,
        cz.name AS citizen_name,
        cz.address AS citizen_address,
        w.name AS worker_name,
        w.department AS worker_dept,
        a.name AS admin_name
    FROM complaint c
    JOIN citizen cz ON c.citizen_id = cz.citizen_id
    LEFT JOIN worker w ON c.worker_id = w.worker_id
    LEFT JOIN admin a ON c.admin_id = a.admin_id
    WHERE c.complaint_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Complaint not found.");
}

$complaint_data = $result->fetch_assoc();
$stmt->close();

$status_class = strtolower(str_replace('_', '-', $complaint_data['status']));
$priority_class = strtolower($complaint_data['severity']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Details - C<?php echo str_pad($complaint_id, 3, '0', STR_PAD_LEFT); ?></title>

    <style>
        :root {
            --primary-blue: #007bff;
            --success-green: #28a745;
            --danger-red: #dc3545;
            --warning-orange: #ffc107;
            --background-light: #f8f9fa;
            --text-dark: #212529;
            --border-color: #dee2e6;
            --card-bg: #ffffff;
            --text-muted: #6c757d;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
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
            text-decoration: none;
            color: var(--text-dark);
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            margin-left: 20px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            color: var(--primary-blue);
        }

        .notifications-badge {
            background-color: var(--danger-red);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: 4px;
        }

        .details-container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .details-card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background: var(--primary-blue);
        }

        .status-badge.in-progress {
            background: var(--warning-orange);
        }

        .status-badge.resolved {
            background: var(--success-green);
        }

        .status-badge.rejected {
            background: var(--danger-red);
        }

        .detail-group {
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 4px solid var(--border-color);
        }

        .detail-row {
            display: flex;
            margin-bottom: 6px;
        }

        .detail-label {
            width: 150px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .priority-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            text-transform: capitalize;
        }

        .priority-badge.high,
        .priority-badge.critical {
            background: #f8d7da;
            color: var(--danger-red);
        }

        .priority-badge.medium {
            background: #fff3cd;
            color: #856404;
        }

        .priority-badge.low {
            background: #d1ecf1;
            color: #0c5460;
        }

        .description-box {
            background: var(--background-light);
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: var(--border-color);
            text-decoration: none;
            color: black;
            border-radius: 6px;
        }

        .back-btn:hover {
            background: var(--primary-blue);
            color: white;
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
    </style>
</head>

<body>
    <header class="header">
        <a href="home.php" class="logo"><span class="logo-icon"></span><span>MCCCTS - Admin</span></a>
        <nav class="nav-links">
            <a href="AllComplaints.php" class="nav-link"><span class="icon icon-doc"></span>All Complaints</a>
            <a href="AssignedComplaints.php" class="nav-link"><span class="icon icon-users"></span>Assigned
                Complaints</a>
            <a href="Notifications.php"
                class="nav-link<?php if (basename($_SERVER['PHP_SELF']) === 'Notifications.php')
                    echo ' active'; ?>"><span
                    class="icon icon-bell"></span>Notifications<?php if ($notification_count > 0): ?><span
                        class="notifications-badge"><?php echo htmlspecialchars($notification_count); ?></span><?php endif; ?></a>
            <a href="profile.php" class="nav-link"><span class="icon icon-profile"></span>Profile</a>
        </nav>
    </header>

    <main class="details-container">
        <div class="details-card">

            <div class="page-header">
                <h1>Complaint Details (ID:
                    C<?php echo str_pad($complaint_data['complaint_id'], 3, '0', STR_PAD_LEFT); ?>)</h1>
                <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $complaint_data['status'])); ?>
                </span>
            </div>

            <div class="detail-group">
                <div class="detail-row"><span
                        class="detail-label">Category:</span><?php echo htmlspecialchars($complaint_data['category']); ?>
                </div>
                <div class="detail-row"><span class="detail-label">Severity:</span>
                    <span class="priority-badge <?php echo $priority_class; ?>">
                        <?php echo htmlspecialchars($complaint_data['severity']); ?> Priority
                    </span>
                </div>
                <div class="detail-row"><span
                        class="detail-label">Location:</span><?php echo htmlspecialchars($complaint_data['location']); ?>
                </div>
                <div class="detail-row"><span class="detail-label">Filed
                        Date:</span><?php echo date('F j, Y', strtotime($complaint_data['filed_date'])); ?></div>
            </div>

            <div class="detail-group">
                <div class="detail-row"><span
                        class="detail-label">Admin:</span><?php echo $complaint_data['admin_name'] ?? 'Unassigned'; ?>
                </div>
                <div class="detail-row"><span
                        class="detail-label">Worker:</span><?php echo $complaint_data['worker_name'] ?? 'Unassigned'; ?>
                </div>
            </div>

            <div class="detail-group">
                <h3>Description</h3>
                <div class="description-box"><?php echo nl2br(htmlspecialchars($complaint_data['description'])); ?>
                </div>
            </div>

            <a href="javascript:history.back()" class="back-btn">← Back</a>

        </div>
    </main>
</body>

</html>