<?php
/** client_update.php - UPDATE a parent record (client). */
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_login();   // not logged in -> redirected to login.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');

$id = (int) ($_POST['client_id'] ?? 0);
[$d, $err] = validateClient($_POST);
if ($err) { $_SESSION['errors'] = $err; $_SESSION['old'] = $d; redirect("client_edit.php?id=$id"); }

try {
    $stmt = $pdo->prepare("UPDATE clients SET company_name = :company_name, contact_person = :contact_person,
                                  email = :email, phone_number = :phone_number, industry = :industry,
                                  updated_by = :updated_by, last_updated = NOW()   -- WHO + WHEN
                           WHERE client_id = :client_id");
    $d['client_id']  = $id;
    $d['updated_by'] = current_user_id();
    $stmt->execute($d);
    redirect('index.php', 'success', 'Client updated.');
} catch (PDOException $ex) {
    $_SESSION['errors'] = [$ex->errorInfo[1] === 1062 ? 'That email belongs to another client.' : 'Database error: ' . $ex->getMessage()];
    $_SESSION['old'] = $d;
    redirect("client_edit.php?id=$id");
}
