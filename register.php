<?php
// register.php
session_start();

// ——— If already logged in, redirect based on role ———
if (isset($_SESSION['user_id'], $_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'Admin':
            header("Location: admDashSUBMITTEDART.php");
            break;
        case 'IT Personnel':
            header("Location: itpASSIGNEDREP.html");
            break;
        default:
            header("Location: cybersafeHOME.html");
            break;
    }
    exit;
}
require 'db.php';  // connects to cybersafeusep.users

$departments = [
    "College of Agriculture and Related Sciences",
    "College of Arts and Sciences",
    "College of Business Administration",
    "College of Development Management",
    "College of Education",
    "College of Engineering",
    "College of Teacher Education and Technology",
    "College of Technology",
    "College of Information and Computing",
    "College of Applied Economics",
    "School of Law",
    "Office of the University Registrar",
    "Office of Student Affairs & Services (OSAS)",
    "Admissions Office",
    "Accounting Office",
    "Human Resource Management Office (HRMO)",
    "Procurement Office"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirmPassword'] ?? '';
    $college  = trim($_POST['college_department'] ?? '');

    // 1) Basic validations
    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !preg_match('/@usep\.edu\.ph$/i', $email)
    ) {
        $error = "Enter a valid USeP email (username@usep.edu.ph).";
    } elseif (empty($username)) {
        $error = "Username cannot be blank.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = "Username must be 3–20 characters and alphanumeric (underscores allowed).";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!in_array($college, $departments, true)) {
        $error = "Please select a valid college or department.";
    }

    // 2) Determine userType by checking three tables
    if (empty($error)) {
        $userType = null;

        // student?
        $stmt = $pdo->prepare("SELECT 1 FROM `usepemails`.`studentemails` WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $userType = 'Student';
        } else {
            // staff?
            $stmt = $pdo->prepare("SELECT 1 FROM `usepemails`.`staff` WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $userType = 'Staff';
            } else {
                // faculty?
                $stmt = $pdo->prepare("SELECT 1 FROM `usepemails`.`faculty` WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $userType = 'Faculty';
                } else {
                    $error = "That email isn’t in the USeP system.";
                }
            }
        }
    }

    // 3) Ensure not already in cybersafeusep.users
    if (empty($error)) {
        $stmt = $pdo->prepare("SELECT 1 FROM `users` WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = "This email or username is already registered.";
        }
    }

    // 4) Insert new user into cybersafeusep.users
    if (empty($error) && $userType !== null) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO `users`
              (userType, username, email, passwordHash, college_department, dateJoined)
            VALUES
              (?,        ?,        ?,     ?,            ?,                   NOW())
        ");
        $stmt->execute([$userType, $username, $email, $hash, $college]);
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Register | CyberSafe USeP</title>
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
    <h2>CyberSafe USeP Register</h2>

    <?php if (!empty($error)): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="registerForm" method="POST" action="register.php">
      <div class="form-group">
        <label for="email">USeP Email</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          placeholder="username@usep.edu.ph"
          required
          value="<?= htmlspecialchars($email ?? '') ?>"
        >
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          placeholder="Enter your username"
          required
          value="<?= htmlspecialchars($username ?? '') ?>"
        >
      </div>

      <div class="form-group">
        <label for="college_department">College / Department</label>
        <select id="college_department" name="college_department" class="form-control" required>
          <option value="">-- Select your college or department --</option>
          <?php foreach ($departments as $dept): ?>
            <option value="<?= htmlspecialchars($dept) ?>"
              <?= (isset($college) && $college === $dept) ? 'selected' : '' ?>>
              <?= htmlspecialchars($dept) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          placeholder="Enter your password (min. 8 characters)"
          required
        >
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input
          type="password"
          id="confirmPassword"
          name="confirmPassword"
          class="form-control"
          placeholder="Confirm your password"
          required
        >
      </div>

      <div class="form-group">
        <button type="submit" class="login-btn">Register</button>
      </div>

      <div class="login-footer">
        <a href="login.php">Login instead?</a>
      </div>
    </form>
  </div>

</body>
</html>
