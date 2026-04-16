<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    session_unset();
    session_destroy();
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple hardcoded admin for testing
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | ScholarMatch</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 6rem auto;
            padding: 3rem;
            animation: fadeIn 0.5s ease;
        }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary); }
        .form-group input { width: 100%; padding: 0.8rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-size: 1rem; }
        .submit-btn {
            background: var(--primary); color: white; border: none; padding: 1rem; font-size: 1.1rem; font-weight: 600; border-radius: 8px; cursor: pointer; width: 100%;
        }
        .error { color: var(--danger); margin-bottom: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="login-container glass-panel">
            <h2 style="text-align: center; margin-bottom: 2rem;">Admin Login</h2>
            <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
            <form id="loginForm" method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" autocomplete="off" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" autocomplete="new-password" required>
                </div>
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
    </div>
    <script>
        window.onload = function() {
            document.getElementById("loginForm").reset();
        };

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                document.getElementById("loginForm").reset();
            }
        });
    </script>
</body>
</html>
