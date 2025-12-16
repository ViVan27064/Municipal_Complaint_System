<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['citizen_id'])) {
    header("Location: LoginAndSignup/login.html");
    exit();
}

$logged_in_citizen_id = $_SESSION['citizen_id'];

$name_sql = "SELECT name FROM citizen WHERE citizen_id = ?";
$name_stmt = $conn->prepare($name_sql);
$name_stmt->bind_param("i", $logged_in_citizen_id);
$name_stmt->execute();
$name_result = $name_stmt->get_result();
if ($name_row = $name_result->fetch_assoc()) {
    $citizen_name = $name_row['name'];
}
$name_stmt->close();

$complaints_sql = "
    SELECT 
        c.complaint_id, 
        c.category, 
        c.description,
        c.severity, 
        c.status, 
        c.location, 
        c.filed_date, 
        c.resolved_date,
        w.name AS worker_name
    FROM complaint c
    LEFT JOIN worker w ON c.worker_id = w.worker_id
    WHERE c.citizen_id = ? 
    AND c.status IN ('pending', 'in_progress')
    ORDER BY c.filed_date DESC
";

$complaints_stmt = $conn->prepare($complaints_sql);
$complaints_stmt->bind_param("i", $logged_in_citizen_id);
$complaints_stmt->execute();
$complaints_result = $complaints_stmt->get_result();

$notification_sql = "SELECT COUNT(*) AS unread_notifications FROM citizen_notification WHERE citizen_id = ? AND status = 'unread'";
$notification_stmt = $conn->prepare($notification_sql);
$notification_stmt->bind_param("i", $logged_in_citizen_id);
$notification_stmt->execute();
$notification_result = $notification_stmt->get_result();
$notification_data = $notification_result->fetch_assoc();
$notification_count = $notification_data['unread_notifications'] ?? 0;
$notification_stmt->close();

$tab_counts_sql = "
    SELECT 
        COUNT(CASE WHEN status IN ('pending', 'in_progress') THEN 1 END) AS pending_count,
        COUNT(CASE WHEN status IN ('resolved', 'rejected') THEN 1 END) AS completed_count
    FROM complaint
    WHERE citizen_id = ?
";
$tab_counts_stmt = $conn->prepare($tab_counts_sql);
$tab_counts_stmt->bind_param("i", $logged_in_citizen_id);
$tab_counts_stmt->execute();
$tab_counts_result = $tab_counts_stmt->get_result();
$tab_counts = $tab_counts_result->fetch_assoc();
$pending_count = $tab_counts['pending_count'] ?? 0;
$completed_count = $tab_counts['completed_count'] ?? 0;
$tab_counts_stmt->close();


// $conn->close(); 

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints</title>
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

        .icon {
            display: inline-block;
            width: 1em;
            height: 1em;
            margin-right: 8px;
            transform: translateY(-5px);
            vertical-align: middle;
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
            justify-content: center;
            font-size: 0.95rem;
            border-radius: 4px;
        }

        .nav-link:hover {
            color: var(--primary-blue);
        }

        .nav-link.active {
            background-color: var(--primary-blue);
            color: white;
        }

        .notifications-badge {
            background-color: var(--danger-red);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            margin-left: 4px;
        }

        .dashboard-container {
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .complaint-tabs {
            display: flex;
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 8px 15px;
            font-size: 0.95rem;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s, border-color 0.2s;
        }

        .tab-button:first-child {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
        }

        .tab-button:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
        }

        .tab-button.active-tab {
            background-color: var(--primary-blue);
            color: white;
            border-color: var(--primary-blue);
        }

        .complaint-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 600;
        }

        .card-id {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            color: white;
            text-transform: uppercase;
            margin-left: 10px;
        }

        .status-badge.pending {
            background-color: var(--primary-blue);
        }

        .status-badge.in_progress {
            background-color: var(--warning-orange);
        }

        .status-badge.resolved {
            background-color: var(--success-green);
        }

        .status-badge.rejected {
            background-color: var(--danger-red);
        }

        .priority-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 5px;
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

        .status-and-priority {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .card-details {
            display: flex;
            flex-direction: column;
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .detail-line {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }

        .detail-line .detail-icon {
            margin-right: 5px;
            color: #ccc;
        }

        .card-footer {
            border-top: 1px solid #f1f1f1;
            padding-top: 15px;
            text-align: center;
        }

        .view-details-btn {
            background: none;
            border: 1px solid var(--border-color);
            color: var(--text-dark);
            padding: 10px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .view-details-btn:hover {
            background-color: var(--background-light);
        }

        .icon-map::before {
            content: '📍';
        }

        .icon-date::before {
            content: '📅';
        }

        .icon-user::before {
            content: '👤';
        }

        .icon-register::before {
            content: '+';
            font-weight: bold;
        }

        .icon-my-complaints::before {
            content: '📄';
        }

        .icon-notifications::before {
            content: '🔔';
        }

        .icon-profile::before {
            content: '👤';
        }
    </style>
</head>

<body>
    <header class="header">
        <a href="../home.php" class="logo">MCCCTS Citizen</a>
        <nav class="nav-links">
            <a href="../RegisterComplaint/file_complaint.php" class="nav-link"><span class="icon icon-register"></span>
                Register Complaint</a>
            <a href="../MyComplaints/MyComplaintsPend.php" class="nav-link active"><span
                    class="icon icon-my-complaints"></span> My Complaints</a>
            <a href="../NotificationsCitizen/Notifications.php" class="nav-link">
                <span class="icon icon-notifications"></span> Notifications
                <?php if ($notification_count > 0): ?>
                    <span class="notifications-badge"><?php echo htmlspecialchars($notification_count); ?></span>
                <?php endif; ?>
            </a>
            <a href="../profile/profile.php" class="nav-link"><span class="icon icon-profile"></span> Profile</a>
        </nav>
    </header>
    <main class="dashboard-container">
        <h1 class="page-title">My Complaints</h1>

        <div class="complaint-tabs">
            <a href="MyComplaintsPend.php" class="tab-button active-tab">Pending
                (<?php echo htmlspecialchars($pending_count); ?>)</a>
            <a href="MyComplaintsComp.php" class="tab-button">Completed
                (<?php echo htmlspecialchars($completed_count); ?>)</a>
        </div>
        <div class="complaint-list">

            <?php
            $complaints_rows = [];
            while ($row = $complaints_result->fetch_assoc()) {
                $complaints_rows[] = $row;
            }
            ?>

            <?php if (!empty($complaints_rows)): ?>
                <?php foreach ($complaints_rows as $row):
                    $status_class = str_replace('_', '-', strtolower($row['status']));
                    $priority_class = strtolower($row['severity']);
                    ?>

                    <div class="complaint-card">
                        <div class="card-header">
                            <div class="card-title-group">
                                <div class="card-title"><?php echo htmlspecialchars($row['category']); ?></div>
                                <div class="card-id">ID:
                                    C<?php echo str_pad(htmlspecialchars($row['complaint_id']), 3, '0', STR_PAD_LEFT); ?></div>
                            </div>
                            <div class="status-and-priority">
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $row['status'])); ?>
                                </span>
                                <span class="priority-badge <?php echo $priority_class; ?>">
                                    <?php echo htmlspecialchars($row['severity']); ?> Priority
                                </span>
                            </div>
                        </div>
                        <div class="card-details">
                            <div class="detail-line">
                                <span class="detail-icon icon-map"></span> <?php echo htmlspecialchars($row['location']); ?>
                            </div>
                            <div class="detail-line">
                                <span class="detail-icon icon-date"></span> Filed:
                                <?php echo htmlspecialchars($row['filed_date']); ?>
                                <?php if ($row['status'] == 'in_progress'): ?>
                                    | Assigned
                                <?php endif; ?>
                            </div>
                            <?php if ($row['worker_name']): ?>
                                <div class="detail-line">
                                    <span class="detail-icon icon-user"></span> Assigned Worker:
                                    <?php echo htmlspecialchars($row['worker_name']); ?>
                                </div>
                            <?php else: ?>
                                <div class="detail-line">
                                    <span class="detail-icon icon-user"></span> Awaiting Worker Assignment
                                </div>
                            <?php endif; ?>
                            <p style="margin-top: 10px; color: var(--text-dark);">
                                <?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                        </div>
                        <div class="card-footer">
                            <a href="ComplaintDetails.php?id=<?php echo htmlspecialchars($row['complaint_id']); ?>"
                                class="view-details-btn">View Details</a>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="complaint-card" style="text-align: center; color: var(--text-muted);">
                    <p>No pending or in-progress complaints found in your record.</p>
                </div>
            <?php endif; ?>

        </div>
    </main>
</body>

</html>