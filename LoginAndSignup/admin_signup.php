<?php
include '../db.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];

    $sql = "INSERT INTO admin (name, email, password, designation, department) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $password, $designation, $department);

    try {
        mysqli_stmt_execute($stmt);
        header("Location: login.html?success=admin_registered");
        exit;
    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            $error_message = "Email already exists.";
        } else {
            $error_message = "Sign up failed. Please try again.";
        }
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(to bottom, #f0f7ff 0%, #ffffff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem 0;
        }

        .signup-container {
            background: #ffffff;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 480px;
            text-align: center;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1a253c;
            margin-bottom: 2rem;
        }

        .error-message {
            color: #D8000C;
            background-color: #FFD2D2;
            border: 1px solid #D8000C;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 15px;
        }

        form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .signup-button {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 8px;
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .signup-button:hover {
            background-color: #0056b3;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: #555;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="signup-container">
        <h1>Create Admin Account</h1>

        <form method="POST">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter official email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" placeholder="Create a password"
                    required>
            </div>

            <div class="form-group">
                <label for="designation">Designation</label>
                <input type="text" id="designation" name="designation" placeholder="e.g., Senior Officer">
            </div>

            <div class="form-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" placeholder="e.g., Water Supply">
            </div>
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <button type="submit" class="signup-button">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.html">Login here</a>
        </div>
    </div>
</body>

</html>