<?php
/** projects.php?client_id=N - CHILDREN: all projects of ONE client + add-project form. */
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_login();   // not logged in -> redirected to login.php

$clientId = (int) ($_GET['client_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = :id");
$stmt->execute([':id' => $clientId]);
$client = $stmt->fetch();
if (!$client) redirect('index.php', 'error', 'Client not found.');

// Only this client's projects (WHERE client_id = ...) -> the "many" side
$stmt = $pdo->prepare("SELECT p.*,
                              CONCAT(ua.first_name, ' ', ua.last_name, ' (', ua.username, ')') AS added_by_name,
                              CONCAT(uu.first_name, ' ', uu.last_name, ' (', uu.username, ')') AS updated_by_name
                       FROM projects p
                       LEFT JOIN users ua ON ua.user_id = p.added_by
                       LEFT JOIN users uu ON uu.user_id = p.updated_by
                       WHERE p.client_id = :id ORDER BY p.project_id DESC");
$stmt->execute([':id' => $clientId]);
$projects = $stmt->fetchAll();

$old = $_SESSION['old'] ?? []; unset($_SESSION['old']);
$title = 'Projects - ' . $client['company_name'];
include 'includes/header.php';
?>
<div class="bar">
    <h1>Projects of <?= e($client['company_name']) ?></h1>
    <a class="btn" href="index.php">&larr; Back to clients</a>
</div>
<p class="muted">Client #<?= (int) $client['client_id'] ?> &middot; <?= e($client['contact_person']) ?> &middot; <?= e($client['email']) ?></p>

<section class="card">
    <h2>Add Project</h2>
    <form action="project_create.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="client_id" value="<?= $clientId ?>">
        <?php $v = $old; include 'includes/project_fields.php'; ?>
        <button class="btn primary" type="submit">Add Project</button>
    </form>
</section>

<section class="card">
    <h2>Projects (<?= count($projects) ?>)</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Project</th><th>Service</th><th>Status</th><th>Budget</th><th>Start</th><th>Deadline</th><th>Added By</th><th>Last Updated By</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$projects): ?><tr><td colspan="10" class="empty">No projects for this client yet.</td></tr><?php endif; ?>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td><?= e($p['project_id']) ?></td>
                <td><?= e($p['project_name']) ?></td>
                <td><?= e($p['service_type']) ?></td>
                <td><span class="badge <?= e(strtolower(str_replace(' ', '-', $p['status']))) ?>"><?= e($p['status']) ?></span></td>
                <td>&#8369;<?= number_format($p['budget'], 2) ?></td>
                <td><?= e(date('M d, Y', strtotime($p['start_date']))) ?></td>
                <td><?= e(date('M d, Y', strtotime($p['deadline']))) ?></td>
                <td class="audit"><?= e($p['added_by_name']) ?><br><?= e(date('M d, Y h:i A', strtotime($p['date_added']))) ?></td>
                <td class="audit"><?php if ($p['updated_by_name']): ?><?= e($p['updated_by_name']) ?><br><?= e(date('M d, Y h:i A', strtotime($p['last_updated']))) ?><?php else: ?>-<?php endif; ?></td>
                <td class="actions">
                    <a class="btn" href="project_edit.php?id=<?= (int) $p['project_id'] ?>">Edit</a>
                    <form action="project_delete.php" method="post" onsubmit="return confirm('Delete this project?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="project_id" value="<?= (int) $p['project_id'] ?>">
                        <?= csrf_field() ?>
        <input type="hidden" name="client_id" value="<?= $clientId ?>">
                        <button class="btn danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
</main></body></html>
