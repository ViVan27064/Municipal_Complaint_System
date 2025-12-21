<?php
session_start();
include '../db.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../LoginAndSignup/login.html");
    exit();
}
$admin_id = $_SESSION['admin_id'];
$name_sql = "SELECT name FROM admin WHERE admin_id=?";
$name_stmt = $conn->prepare($name_sql);
$name_stmt->bind_param("i", $admin_id);
$name_stmt->execute();
$name_res = $name_stmt->get_result();
$admin_name = $name_res->fetch_assoc()['name'] ?? "Admin";
$name_stmt->close();
$sql = "SELECT 
(SELECT COUNT(*) FROM complaint) AS total_count,
(SELECT COUNT(*) FROM complaint WHERE worker_id IS NULL AND status='pending') AS pending_assign,
(SELECT COUNT(*) FROM complaint WHERE worker_id IS NOT NULL AND status IN('pending','in_progress')) AS assigned_count,
(SELECT COUNT(*) FROM complaint WHERE status='resolved') AS resolved_count,
(SELECT COUNT(*) FROM admin_notification WHERE admin_id=? AND status='unread') AS unread_notifications";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
$total_count = $data['total_count'] ?? 0;
$pending_assign = $data['pending_assign'] ?? 0;
$assigned_count = $data['assigned_count'] ?? 0;
$resolved_count = $data['resolved_count'] ?? 0;
$notification_count = $data['unread_notifications'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Admin Dashboard | MCCCTS</title>
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

        .dashboard-container {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: .98rem;
            color: #6c757d;
            margin-bottom: 28px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 32px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border-color);
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 110px;
        }

        .stat-label {
            font-size: .9rem;
            color: #6c757d;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-meta {
            font-size: .84rem;
            color: #6c757d;
        }

        .stat-total {
            background: linear-gradient(135deg, #e6f0ff, #ffffff);
        }

        .stat-pending {
            background: linear-gradient(135deg, #fff7e6, #ffffff);
        }

        .stat-assigned {
            background: linear-gradient(135deg, #ffe6ea, #ffffff);
        }

        .stat-resolved {
            background: linear-gradient(135deg, #e6ffef, #ffffff);
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .quick-card {
            background-color: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border-color);
            padding: 18px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            color: var(--text-dark);
            transition: .2s;
        }

        .quick-card:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, .07);
            transform: translateY(-2px);
        }

        .quick-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .quick-title {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .quick-desc {
            font-size: .9rem;
            color: #6c757d;
        }

        .quick-icon {
            font-size: 1.8rem;
        }

        .quick-all {
            border-left: 4px solid:var(--primary-blue);
        }

        .quick-assigned {
            border-left: 4px solid:var(--success-green);
        }

        @media(max-width:768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .nav-links {
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
            }

            .nav-link {
                margin-left: 0;
                margin-right: 10px;
                margin-top: 4px;
            }

            .dashboard-container {
                padding: 20px;
            }
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
    <main class="dashboard-container">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Monitor and manage all complaints in the system</p>
        <section class="stats-grid">
            <div class="stat-card stat-total">
                <div>
                    <div class="stat-label"><span>📄</span><span>Total Complaints</span></div>
                    <div class="stat-value"><?php echo htmlspecialchars($total_count); ?></div>
                    <div class="stat-meta">All complaints filed by citizens</div>
                </div>
            </div>
            <div class="stat-card stat-pending">
                <div>
                    <div class="stat-label"><span>⏱️</span><span>Pending Assignment</span></div>
                    <div class="stat-value"><?php echo htmlspecialchars($pending_assign); ?></div>
                    <div class="stat-meta">Complaints waiting for worker assignment</div>
                </div>
            </div>
            <div class="stat-card stat-assigned">
                <div>
                    <div class="stat-label"><span>👷</span><span>Assigned</span></div>
                    <div class="stat-value"><?php echo htmlspecialchars($assigned_count); ?></div>
                    <div class="stat-meta">Complaints assigned to workers</div>
                </div>
            </div>
            <div class="stat-card stat-resolved">
                <div>
                    <div class="stat-label"><span>✔️</span><span>Resolved</span></div>
                    <div class="stat-value"><?php echo htmlspecialchars($resolved_count); ?></div>
                    <div class="stat-meta">Complaints marked as resolved</div>
                </div>
            </div>
        </section>
        <section class="quick-links">
            <a href="AllComplaints.php" class="quick-card quick-all">
                <div class="quick-left">
                    <div class="quick-title">All Complaints</div>
                    <div class="quick-desc">View and assign complaints to workers</div>
                </div>
                <div class="quick-icon">📋</div>
            </a>
            <a href="AssignedComplaints.php" class="quick-card quick-assigned">
                <div class="quick-left">
                    <div class="quick-title">Assigned Complaints</div>
                    <div class="quick-desc">Monitor complaints assigned to workers</div>
                </div>
                <div class="quick-icon">🧑‍🔧</div>
            </a>
        </section>
    </main>
</body>

</html>
