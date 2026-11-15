<?php
/** project_update.php - UPDATE a child record. */
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_login();   // not logged in -> redirected to login.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');

$id = (int) ($_POST['project_id'] ?? 0);
$clientId = (int) ($_POST['client_id'] ?? 0);
[$d, $err] = validateProject($_POST);
if ($err) { $_SESSION['errors'] = $err; $_SESSION['old'] = $d; redirect("project_edit.php?id=$id"); }

$stmt = $pdo->prepare("UPDATE projects SET project_name = :project_name, service_type = :service_type, status = :status,
                              budget = :budget, start_date = :start_date, deadline = :deadline,
                              updated_by = :updated_by, last_updated = NOW()   -- WHO + WHEN
                       WHERE project_id = :project_id");
$d['project_id'] = $id;
$d['updated_by'] = current_user_id();
$stmt->execute($d);
redirect("projects.php?client_id=$clientId", 'success', 'Project updated.');
