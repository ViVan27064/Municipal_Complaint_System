<?php
session_start();
include '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../LoginAndSignup/login.html");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'];

    $sql = "UPDATE admin SET name = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $admin_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }

    $stmt->close();
}

$sql = "SELECT name, email, designation, department FROM admin WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$notif_sql = "SELECT COUNT(*) AS unread FROM admin_notification WHERE admin_id = ? AND status = 'unread'";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("i", $admin_id);
$notif_stmt->execute();
$notification_count = $notif_stmt->get_result()->fetch_assoc()['unread'] ?? 0;
$notif_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <style>
        :root {
            --blue: #007bff;
            --red: #dc3545;
            --bg: #f8f9fa;
            --text: #212529;
            --border: #dee2e6;
            --card: #fff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI';
            margin: 0;
            background: var(--bg);
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
            gap: 12px;
            align-items: center;
        }

        .nav-link {
            padding: 8px 18px;
            border-radius: 999px;
            background: #eef3ff;
            color: var(--text);
            text-decoration: none;
            font-size: .92rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            background: #d8e6ff;
            color: var(--blue);
        }

        .nav-link.active {
            background: var(--blue);
            color: white;
        }

        .notifications-badge {
            background: var(--red);
            padding: 2px 6px;
            border-radius: 50%;
            font-size: .7rem;
            color: white;
        }

        .main {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: var(--card);
            padding: 30px;
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .05);
        }

        h2 {
            margin: 0;
            font-size: 1.5rem;
        }

        .sub {
            font-size: .9rem;
            color: gray;
            margin-top: 6px;
        }

        .form-group {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        input:focus {
            border-color: var(--blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, .15);
        }

        input[readonly] {
            background: #f1f1f1;
            cursor: not-allowed;
        }

        .btn-save {
            width: 100%;
            padding: 12px;
            background: var(--blue);
            color: white;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-save:hover {
            background: #005ad1;
        }

        .btn-logout {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            background: white;
            border: 1px solid var(--red);
            color: var(--red);
            text-align: center;
            display: block;
            margin-top: 22px;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #fff2f2;
        }

        .alert {
            background: #d1e7dd;
            padding: 12px;
            color: #0f5132;
            border-radius: 6px;
            border: 1px solid #badbcc;
            text-align: center;
            margin-bottom: 20px;
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
    </style>
</head>

<body>

    <header class="header">
        <a href="home.php" class="logo"><span class="logo-icon"></span><span>MCCCTS - Admin</span></a>
        <nav class="nav-links">
            <a href="AllComplaints.php" class="nav-link"><span class="icon icon-doc"></span>All Complaints</a>
            <a href="AssignedComplaints.php" class="nav-link"><span class="icon icon-users"></span>Assigned
                Complaints</a>
            <a href="Notifications.php" class="nav-link"><span
                    class="icon icon-bell"></span>Notifications<?php if ($notification_count > 0): ?><span
                        class="notifications-badge"><?php echo htmlspecialchars($notification_count); ?></span><?php endif; ?></a>
            <a href="profile.php" class="nav-link active"><span class="icon icon-profile"></span>Profile</a>
        </nav>
    </header>

    <div class="main">
        <div class="card">

            <h2>Admin Profile</h2>
            <p class="sub">Manage your admin details</p>

            <?php if ($message): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Designation</label>
                    <input type="text" value="<?= htmlspecialchars($user['designation']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <input type="text" value="<?= htmlspecialchars($user['department']) ?>" readonly>
                </div>

                <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
            </form>

            <a href="../LoginAndSignup/login.html" class="btn-logout">Log Out</a>

        </div>
    </div>

</body>

</html>
