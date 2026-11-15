<?php
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (is_logged_in()) redirect('index.php');   // already logged in

$old = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = ['first_name', 'last_name', 'address', 'age', 'email', 'username'];
    foreach ($f as $k) $old[$k] = trim($_POST[$k] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password_confirm'] ?? '';

    $err = [];
    foreach (['first_name' => 'First name', 'last_name' => 'Last name', 'address' => 'Address'] as $k => $label) {
        if ($old[$k] === '') $err[] = "$label is required.";
    }
    if (!ctype_digit($old['age']) || (int) $old['age'] < 1 || (int) $old['age'] > 120) $err[] = 'Age must be a number from 1 to 120.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Email is not valid.';
    if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $old['username'])) $err[] = 'Username must be 4-30 characters: letters, numbers, underscore.';
    if (strlen($password) < 8) $err[] = 'Password must be at least 8 characters.';
    if ($password !== $password2) $err[] = 'Passwords do not match.';

    if (!$err) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, address, age, email, username, password_hash)
                                   VALUES (:first_name, :last_name, :address, :age, :email, :username, :password_hash)");
            $stmt->execute([
                ':first_name' => $old['first_name'], ':last_name' => $old['last_name'],
                ':address'    => $old['address'],    ':age'       => (int) $old['age'],
                ':email'      => $old['email'],      ':username'  => $old['username'],
                // password_hash() creates a salted one-way hash (bcrypt)
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            redirect('login.php', 'success', 'Registration successful! You can now log in.');
        } catch (PDOException $ex) {
            // 1062 = duplicate entry on a UNIQUE column (username or email)
            $err[] = $ex->errorInfo[1] === 1062 ? 'That username or email is already registered.' : 'Database error: ' . $ex->getMessage();
        }
    }
    $_SESSION['errors'] = $err;
}

$title = 'Register';
include 'includes/header.php';
?>
<section class="card narrow">
    <h1>Create an Account</h1>
    <form method="post" action="register.php">
        <?= csrf_field() ?>
        <div class="grid">
            <label>First name <input type="text" name="first_name" maxlength="50" required value="<?= e($old['first_name'] ?? '') ?>"></label>
            <label>Last name <input type="text" name="last_name" maxlength="50" required value="<?= e($old['last_name'] ?? '') ?>"></label>
            <label class="wide">Address <input type="text" name="address" maxlength="150" required value="<?= e($old['address'] ?? '') ?>"></label>
            <label>Age <input type="number" name="age" min="1" max="120" required value="<?= e($old['age'] ?? '') ?>"></label>
            <label>Email <input type="email" name="email" maxlength="100" required value="<?= e($old['email'] ?? '') ?>"></label>
            <label>Username <input type="text" name="username" maxlength="30" required value="<?= e($old['username'] ?? '') ?>"></label>
            <label>Password (min. 8 characters) <input type="password" name="password" required></label>
            <label>Confirm password <input type="password" name="password_confirm" required></label>
        </div>
        <button class="btn primary" type="submit">Register</button>
        <p class="muted">Already have an account? <a href="login.php">Log in</a></p>
    </form>
</section>
</main></body></html>
