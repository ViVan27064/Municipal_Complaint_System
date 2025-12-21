<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['citizen_id'])) {
    header("Location: LoginAndSignup/login.html");
    exit();
}

$logged_in_citizen_id = $_SESSION['citizen_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid complaint ID.");
}
$complaint_id = (int) $_GET['id'];

$complaint_sql = "
    SELECT 
        c.complaint_id, 
        c.category, 
        c.description,
        c.severity, 
        c.status, 
        c.location, 
        c.filed_date, 
        c.resolved_date,
        w.name AS worker_name,
        w.department AS worker_dept,
        a.name AS admin_name,
        cz.name AS citizen_name 
    FROM complaint c
    LEFT JOIN worker w ON c.worker_id = w.worker_id
    LEFT JOIN admin a ON c.admin_id = a.admin_id
    JOIN citizen cz ON c.citizen_id = cz.citizen_id
    WHERE c.complaint_id = ? AND c.citizen_id = ?
";

$complaint_stmt = $conn->prepare($complaint_sql);
$complaint_stmt->bind_param("ii", $complaint_id, $logged_in_citizen_id);
$complaint_stmt->execute();
$complaint_result = $complaint_stmt->get_result();

$complaint_data = $complaint_result->fetch_assoc();
if (!$complaint_data) {
    die("Complaint not found or unauthorized access.");
}
$complaint_stmt->close();

$notification_sql = "SELECT COUNT(*) AS unread_notifications FROM citizen_notification WHERE citizen_id = ? AND status = 'unread'";
$notification_stmt = $conn->prepare($notification_sql);
$notification_stmt->bind_param("i", $logged_in_citizen_id);
$notification_stmt->execute();
$notification_result = $notification_stmt->get_result();
$notification_data = $notification_result->fetch_assoc();
$notification_count = $notification_data['unread_notifications'] ?? 0;
$notification_stmt->close();

$status_class = str_replace('_', '-', strtolower($complaint_data['status'] ?? ''));
$priority_class = strtolower($complaint_data['severity'] ?? '');
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
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
        }

        .icon {
            display: inline-block;
            width: 1em;
            height: 1em;
            margin-right: 8px;
            transform: translateY(-5px);
            vertical-align: middle;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-dark);
            margin-left: 20px;
            padding: 5px 10px;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            border-radius: 4px;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-bottom: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }

        .status-badge.pending { background-color: var(--primary-blue); }
        .status-badge.in-progress { background-color: var(--warning-orange); }
        .status-badge.resolved { background-color: var(--success-green); }
        .status-badge.rejected { background-color: var(--danger-red); }

        .detail-group {
            margin-bottom: 20px;
            border-left: 4px solid var(--border-color);
            padding-left: 15px;
        }

        .detail-group h3 {
            font-size: 1.1rem;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-muted);
            width: 150px;
            flex-shrink: 0;
        }

        .priority-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .priority-badge.critical,
        .priority-badge.high {
            background-color: #f8d7da;
            color: var(--danger-red);
            border: 1px solid #f5c6cb;
        }

        .priority-badge.medium {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .priority-badge.low {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .description-box {
            background-color: var(--background-light);
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            white-space: pre-wrap;
        }

        .back-btn {
            background-color: var(--border-color);
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin-top: 20px;
            transition: background-color 0.2s;
        }

        .back-btn:hover {
            background-color: var(--primary-blue);
        }

        .icon-map::before { content: '📍'; }
        .icon-date::before { content: '📅'; }
        .icon-user::before { content: '👤'; }
        .icon-register::before { content: '+'; font-weight: bold; }
        .icon-my-complaints::before { content: '📄'; }
        .icon-notifications::before { content: '🔔'; }
        .icon-profile::before { content: '👤'; }
    </style>
</head>

<body>
    <header class="header">
        <a href="../home.php" class="logo">MCCCTS Citizen</a>
        <nav class="nav-links">
            <a href="../RegisterComplaint/file_complaint.php" class="nav-link"><span class="icon icon-register"></span> Register Complaint</a>
            <a href="../MyComplaints/MyComplaintsPend.php" class="nav-link active"><span class="icon icon-my-complaints"></span> My Complaints</a>
            <a href="../NotificationsCitizen/Notifications.php" class="nav-link">
                <span class="icon icon-notifications"></span> Notifications
                <?php if ($notification_count > 0): ?>
                    <span class="notifications-badge"><?php echo htmlspecialchars($notification_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="../profile/profile.php" class="nav-link"><span class="icon icon-profile"></span> Profile</a>
        </nav>
    </header>
    <main class="details-container">
        <div class="details-card">
            <div class="page-header">
                <h1>Complaint Details (ID: C<?php echo str_pad(htmlspecialchars($complaint_data['complaint_id']), 3, '0', STR_PAD_LEFT); ?>)</h1>
                <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $complaint_data['status'])); ?>
                </span>
            </div>

            <div class="detail-group">
                <h3>General Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Category:</span>
                    <span><?php echo htmlspecialchars($complaint_data['category']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Severity:</span>
                    <span>
                        <span class="priority-badge <?php echo $priority_class; ?>">
                            <?php echo htmlspecialchars($complaint_data['severity']); ?> Priority
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Location:</span>
                    <span><?php echo htmlspecialchars($complaint_data['location']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Filed Date:</span>
                    <span><?php echo htmlspecialchars(date('F j, Y', strtotime($complaint_data['filed_date']))); ?></span>
                </div>
                <?php if ($complaint_data['resolved_date']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Resolved Date:</span>
                        <span style="font-weight: 600; color: var(--success-green);"><?php echo htmlspecialchars(date('F j, Y', strtotime($complaint_data['resolved_date']))); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-group">
                <h3>Personnel Assigned</h3>
                <div class="detail-row">
                    <span class="detail-label">Admin In-Charge:</span>
                    <span><?php echo $complaint_data['admin_name'] ? htmlspecialchars($complaint_data['admin_name']) : 'Awaiting Assignment'; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Assigned Worker:</span>
                    <span><?php echo $complaint_data['worker_name'] ? htmlspecialchars($complaint_data['worker_name']) : 'Awaiting Assignment'; ?></span>
                </div>
                <?php if ($complaint_data['worker_dept']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Department:</span>
                        <span><?php echo htmlspecialchars($complaint_data['worker_dept']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-group">
                <h3>Full Description</h3>
                <div class="description-box">
                    <?php echo nl2br(htmlspecialchars($complaint_data['description'])); ?>
                </div>
            </div>

            <a href="javascript:history.back()" class="back-btn">← Back to My Complaints</a>
        </div>
    </main>
</body>

</html>
