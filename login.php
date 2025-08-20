<?php
// login.php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'Admin':
            header("Location: admDashSUBMITTEDART.php");
            break;
        case 'IT Personnel':
            header("Location: itpASSIGNEDREP.php");
            break;
        default:
            header("Location: cybersafeHOME.php");
            break;
    }
    exit;
}

require 'db.php';

$error = '';
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1) Determine if we should allow a non‑UseP domain (Admin/IT only)
    $allowNonUsep = false;
    $checkType = $pdo->prepare("SELECT userType FROM users WHERE email = ?");
    $checkType->execute([$email]);
    $typeRow = $checkType->fetch(PDO::FETCH_ASSOC);
    if ($typeRow && in_array($typeRow['userType'], ['Admin', 'IT Personnel'])) {
        $allowNonUsep = true;
    }

    // 2) Basic format check
    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || (!$allowNonUsep && !preg_match('/@usep\.edu\.ph$/i', $email))
    ) {
        $error = "Enter a valid USeP email (username@usep.edu.ph).";
    } else {
        // 3) Look up user
        $stmt = $pdo->prepare("
            SELECT userID, username, userType, passwordHash
              FROM users
             WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 4) Verify password using bcrypt only
        if (!$user || !password_verify($password, $user['passwordHash'])) {
            $error = "Invalid email or password.";
        }
    }

    // 5) On success, store in session and redirect based on role
    if (empty($error)) {
        $_SESSION['user_id']   = $user['userID'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['user_type'] = $user['userType'];

        switch ($user['userType']) {
            case 'Admin':
                header("Location: admDashSUBMITTEDART.php");
                break;
            case 'IT Personnel':
                header("Location: itpASSIGNEDREP.php");
                break;
            default:
                header("Location: cybersafeHOME.php");
                break;
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Login | CyberSafe USeP</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
    body { background:#f5f5f5; color:#333; }
    .login-header { background:maroon; padding:1rem 2rem; text-align:center; }
    .login-header img { height:50px; }
    .login-container {
      max-width:400px; margin:3rem auto; padding:2rem;
      background:#fff; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1);
    }
    .login-container h2 { color:maroon; text-align:center; margin-bottom:1.5rem; }
    .form-group { margin-bottom:1.5rem; }
    .form-group label { display:block; margin-bottom:0.5rem; font-weight:bold; }
    .form-control {
      width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; font-size:1rem;
    }
    .login-btn {
      width:100%; background:maroon; color:#fff; border:none;
      padding:12px; font-size:1rem; border-radius:5px; cursor:pointer;
      transition:background 0.3s;
    }
    .login-btn:hover { background:#b30000; }
    .login-footer { text-align:center; margin-top:1.5rem; }
    .login-footer a { color:maroon; text-decoration:none; }
    .login-footer a:hover { text-decoration:underline; }
    .error-message {
      color:#dc3545; font-size:0.875rem; margin-top:0.25rem; display:block;
    }
    @media(max-width:480px) {
      .login-container { margin:2rem 1rem; padding:1.5rem; }
    }
  </style>
</head>
<body>

  <header class="login-header">
    <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
  </header>

  <div class="login-container">
    <h2>CyberSafe USeP Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="email">USeP Email</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          placeholder="username@usep.edu.ph"
          required
          value="<?= htmlspecialchars($email) ?>"
        >
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          placeholder="Enter your password"
          required
        >
      </div>

      <div class="form-group">
        <button type="submit" class="login-btn">Login</button>
      </div>

      <div class="login-footer">
        <a href="forgot-password.php">Forgot password?</a> |
        <a href="register.php">Register</a>
      </div>
    </form>
  </div>

</body>
</html>
