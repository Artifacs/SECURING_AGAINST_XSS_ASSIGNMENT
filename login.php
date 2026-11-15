<?php
/** login.php - USER LOGIN: checks username + password, then starts the user's session. */
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (is_logged_in()) redirect('index.php');   // already logged in

$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT user_id, username, first_name, last_name, password_hash FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    // password_verify() compares the typed password with the stored hash
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);   // new session id after login (prevents session fixation)
        $_SESSION['user_id']   = (int) $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
        redirect('index.php', 'success', 'Welcome back, ' . $user['first_name'] . '!');
    }
    // Same message for wrong username OR wrong password (don't reveal which)
    $_SESSION['errors'] = ['Incorrect username or password.'];
}

$title = 'Login';
include 'includes/header.php';
?>
<section class="card narrow">
    <h1>Login</h1>
    <form method="post" action="login.php">
        <?= csrf_field() ?>
        <div class="grid one">
            <label>Username <input type="text" name="username" required autofocus value="<?= e($username) ?>"></label>
            <label>Password <input type="password" name="password" required></label>
        </div>
        <button class="btn primary" type="submit">Log in</button>
        <p class="muted">No account yet? <a href="register.php">Register here</a></p>
    </form>
</section>
</main></body></html>
