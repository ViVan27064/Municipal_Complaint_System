<?php
session_start();
include '../db.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../LoginAndSignup/login.html");
    exit();
}
$admin_id = $_SESSION['admin_id'];

$sql = "SELECT admin_notification_id,message,date_time,status FROM admin_notification WHERE admin_id=? ORDER BY date_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$count_sql = "SELECT COUNT(*) AS unread_count FROM admin_notification WHERE admin_id=? AND status='unread'";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $admin_id);
$count_stmt->execute();
$notification_count = $count_stmt->get_result()->fetch_assoc()['unread_count'] ?? 0;
$count_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Notifications | MCCCTS Admin</title>
    <style>
        :root {
            --primary-blue: #007bff;
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --background-light: #f8f9fa;
            --danger-red: #dc3545;
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
            background-color: #e6f0ff;
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

        .notifications-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .notification-card {
            background: var(--card-bg);
            border: 1px solid #e0e4ed;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
            transition: .2s;
        }

        .notification-card.unread {
            background: #f4f7ff;
            border-left: 4px solid var(--primary-blue);
        }

        .notification-card:hover {
            background: #f0f4ff;
            transform: translateY(-1px);
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
        }

        .icon-wrapper {
            background: #eef3ff;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary-blue);
            margin-right: 14px;
        }

        .notification-text {
            font-size: .95rem;
            color: var(--text-dark);
        }

        .notification-date {
            font-size: .84rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .badge-new {
            background: var(--primary-blue);
            color: #fff;
            font-size: .75rem;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 500;
            margin-right: 8px;
        }

        .btn-tick {
            background: transparent;
            border: 2px solid:#28a745;
            color: #28a745;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1rem;
            transition: .2s;
            padding: 0;
        }

        .btn-tick:hover {
            background: #28a745;
            color: #fff;
            transform: scale(1.05);
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @media(max-width:768px) {
            .notifications-container {
                margin: 20px auto;
            }

            .notification-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .actions {
                align-self: flex-end;
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
    <main class="notifications-container">
        <h1>Notifications</h1>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="notification-card<?php if ($row['status'] === 'unread')
                    echo ' unread'; ?>">
                    <div class="notification-content">
                        <div class="icon-wrapper">
                            <?php
                            $msg = strtolower($row['message']);
                            if (strpos($msg, 'resolved') !== false)
                                echo '✅';
                            elseif (strpos($msg, 'assigned') !== false)
                                echo '👷';
                            elseif (strpos($msg, 'high priority') !== false)
                                echo '⚠️';
                            else
                                echo '🔔';
                            ?>
                        </div>
                        <div>
                            <div class="notification-text"><?php echo htmlspecialchars($row['message']); ?></div>
                            <div class="notification-date"><?php echo htmlspecialchars($row['date_time']); ?></div>
                        </div>
                    </div>
                    <div class="actions">
                        <?php if ($row['status'] === 'unread'): ?>
                            <span class="badge-new">New</span>
                            <form action="mark_read.php" method="post">
                                <input type="hidden" name="notif_id" value="<?php echo $row['admin_notification_id']; ?>">
                                <button type="submit" class="btn-tick" title="Mark as read">✔</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:var(--text-muted);">No notifications found.</p>
        <?php endif; ?>
    </main>
</body>

</html>