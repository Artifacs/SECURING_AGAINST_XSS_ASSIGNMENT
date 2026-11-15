<?php
require_once 'config/dbconfig.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_login();   // not logged in -> redirected to login.php

$title = 'Clients';
$old = $_SESSION['old'] ?? []; unset($_SESSION['old']);

// LEFT JOIN + COUNT shows how many projects (children) each client (parent) has
$clients = $pdo->query(
    "SELECT c.*, COUNT(p.project_id) AS total_projects,
            CONCAT(ua.first_name, ' ', ua.last_name, ' (', ua.username, ')') AS added_by_name,
            CONCAT(uu.first_name, ' ', uu.last_name, ' (', uu.username, ')') AS updated_by_name
     FROM clients c
     LEFT JOIN projects p ON p.client_id = c.client_id
     LEFT JOIN users ua ON ua.user_id = c.added_by       -- who inserted
     LEFT JOIN users uu ON uu.user_id = c.updated_by     -- who last updated
     GROUP BY c.client_id ORDER BY c.client_id DESC"
)->fetchAll();

include 'includes/header.php';
?>
<h1>Data Engineering Consulting - Clients</h1>
<p class="muted">One client can have many projects.</p>

<section class="card">
    <h2>Add Client</h2>
    <form action="client_create.php" method="post">
        <?= csrf_field() ?>
        <?php $v = $old; include 'includes/client_fields.php'; ?>
        <button class="btn primary" type="submit">Add Client</button>
    </form>
</section>

<section class="card">
    <h2>All Clients (<?= count($clients) ?>)</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Company</th><th>Contact</th><th>Email</th><th>Phone</th><th>Industry</th><th>Projects</th><th>Added By</th><th>Last Updated By</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$clients): ?><tr><td colspan="10" class="empty">No clients yet.</td></tr><?php endif; ?>
        <?php foreach ($clients as $c): ?>
            <tr>
                <td><?= e($c['client_id']) ?></td>
                <td><?= e($c['company_name']) ?></td>
                <td><?= e($c['contact_person']) ?></td>
                <td><?= e($c['email']) ?></td>
                <td><?= e($c['phone_number']) ?></td>
                <td><?= e($c['industry']) ?></td>
                <td><?= (int) $c['total_projects'] ?></td>
                <td class="audit"><?= e($c['added_by_name']) ?><br><?= e(date('M d, Y h:i A', strtotime($c['date_added']))) ?></td>
                <td class="audit"><?php if ($c['updated_by_name']): ?><?= e($c['updated_by_name']) ?><br><?= e(date('M d, Y h:i A', strtotime($c['last_updated']))) ?><?php else: ?>-<?php endif; ?></td>
                <td class="actions">
                    <a class="btn" href="projects.php?client_id=<?= (int) $c['client_id'] ?>">Projects</a>
                    <a class="btn" href="client_edit.php?id=<?= (int) $c['client_id'] ?>">Edit</a>
                    <form action="client_delete.php" method="post"
                          data-confirm="Delete this client AND all of its <?= (int) $c['total_projects'] ?> project(s)?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="client_id" value="<?= (int) $c['client_id'] ?>">
                        <button class="btn danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>
</main></body></html>

