<?php


const STATUSES = ['Planned', 'In Progress', 'Completed'];

// first codes 
function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); } // My main defense 

function has_html(string $v): bool { return (bool) preg_match('/[<>]/', $v); } //Input validation

function reject_html(array $d, array $labels, array &$err): void {
    foreach ($labels as $f => $label) {
        if (has_html($d[$f] ?? '')) $err[] = "$label must not contain < or > (HTML/script is not allowed).";
    }
}



//Strong password rule
function password_errors(string $pw): array {
    $err = [];
    if (strlen($pw) < 8)             $err[] = 'Password must be at least 8 characters.';
    if (strlen($pw) > 72)            $err[] = 'Password must be at most 72 characters.';  
    if (!preg_match('/[A-Z]/', $pw)) $err[] = 'Password must contain at least one UPPERCASE letter.';
    if (!preg_match('/[a-z]/', $pw)) $err[] = 'Password must contain at least one lowercase letter.';
    if (!preg_match('/[0-9]/', $pw)) $err[] = 'Password must contain at least one number.';
    return $err;
}









function redirect(string $url, string $type = '', string $msg = ''): never {
    if ($msg !== '') { if (session_status() === PHP_SESSION_NONE) session_start(); $_SESSION['flash'] = ['type' => $type, 'message' => $msg]; }
    header("Location: $url"); exit;
}

function validateClient(array $p): array {
    $d = [];
    foreach (['company_name','contact_person','email','phone_number','industry'] as $f) $d[$f] = trim($p[$f] ?? '');
    $err = [];
    foreach ($d as $f => $v) if ($v === '') $err[] = ucwords(str_replace('_', ' ', $f)) . ' is required.';
    if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err[] = 'Email is not valid.';
    reject_html($d, ['company_name' => 'Company name', 'contact_person' => 'Contact person', 'phone_number' => 'Phone number', 'industry' => 'Industry'], $err);   // XSS defense #2
    return [$d, $err];
}

/** Reads + validates the PROJECT (child) form. Returns [data, errors]. */
function validateProject(array $p): array {
    $d = [];
    foreach (['project_name','service_type','status','budget','start_date','deadline'] as $f) $d[$f] = trim($p[$f] ?? '');
    $err = [];
    if ($d['project_name'] === '') $err[] = 'Project name is required.';
    if ($d['service_type'] === '') $err[] = 'Service type is required.';
    reject_html($d, ['project_name' => 'Project name', 'service_type' => 'Service type'], $err);   // XSS defense #2
    if (!in_array($d['status'], STATUSES, true)) $err[] = 'Choose a valid status.';
    if (!is_numeric($d['budget']) || (float) $d['budget'] < 0) $err[] = 'Budget must be a positive number.';
    foreach (['start_date', 'deadline'] as $f) {
        $dt = DateTime::createFromFormat('Y-m-d', $d[$f]);
        if (!$dt || $dt->format('Y-m-d') !== $d[$f]) $err[] = ucwords(str_replace('_', ' ', $f)) . ' must be a valid date.';
    }
    if (!$err && $d['deadline'] < $d['start_date']) $err[] = 'Deadline cannot be before the start date.';
    return [$d, $err];
}
