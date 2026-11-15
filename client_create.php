<?php
/** client_create.php - INSERT a parent record (client). */
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_login();   // not logged in -> redirected to login.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');

[$d, $err] = validateClient($_POST);
if ($err) { $_SESSION['errors'] = $err; $_SESSION['old'] = $d; redirect('index.php'); }

try {
    $stmt = $pdo->prepare("INSERT INTO clients (company_name, contact_person, email, phone_number, industry, added_by)
                           VALUES (:company_name, :contact_person, :email, :phone_number, :industry, :added_by)");
    $d['added_by'] = current_user_id();   // WHO inserted this record
    $stmt->execute($d);
    redirect('index.php', 'success', 'Client added.');
} catch (PDOException $ex) {
    $_SESSION['errors'] = [$ex->errorInfo[1] === 1062 ? 'A client with that email already exists.' : 'Database error: ' . $ex->getMessage()];
    $_SESSION['old'] = $d;
    redirect('index.php');
}
