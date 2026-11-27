<?php
// POST /api/profile.php - saves a "Free profile evaluation" submission and emails a notification.
declare(strict_types=1);
require __DIR__ . '/lib.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    json_out(405, ['ok' => false, 'error' => 'Use POST']);
}

$raw = file_get_contents('php://input', false, null, 0, 40000) ?: '';
$in = json_decode($raw, true);
if (!is_array($in)) {
    $in = $_POST;
}

if (clean($in['website'] ?? '', 200) !== '') { // bot trap
    json_out(200, ['ok' => true]);
}

function pick(array $in, string $k, array $allowed): string
{
    $v = clean($in[$k] ?? '', 60);
    return in_array($v, $allowed, true) ? $v : '';
}

function pick_many(array $in, string $k, array $allowed): string
{
    $v = $in[$k] ?? [];
    if (!is_array($v)) {
        return '';
    }
    $out = [];
    foreach ($v as $item) {
        $item = clean($item, 60);
        if (in_array($item, $allowed, true) && !in_array($item, $out, true)) {
            $out[] = $item;
        }
    }
    return implode(', ', $out);
}

$p = [
    'name'          => clean($in['name'] ?? '', 120),
    'email'         => clean($in['email'] ?? '', 160),
    'phone'         => clean($in['phone'] ?? '', 30),
    'city'          => clean($in['city'] ?? '', 80),
    'degree'        => clean($in['degree'] ?? '', 80),
    'college'       => clean($in['college'] ?? '', 160),
    'grad_year'     => clean($in['grad_year'] ?? '', 4),
    'work_years'    => pick($in, 'work_years', ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10+']),
    'role'          => clean($in['role'] ?? '', 160),
    'industry'      => clean($in['industry'] ?? '', 80),
    'gmat_status'   => pick($in, 'gmat_status', ['Not started', 'Preparing', 'Taken']),
    'gmat_score'    => clean($in['gmat_score'] ?? '', 3),
    'target_score'  => clean($in['target_score'] ?? '', 3),
    'test_date'     => clean($in['test_date'] ?? '', 7),
    'programs'      => pick_many($in, 'programs', ['2-year MBA', '1-year MBA', 'Executive MBA', 'Masters in Management', 'Not sure yet']),
    'countries'     => pick_many($in, 'countries', ['India', 'USA', 'UK', 'Europe', 'Canada', 'Singapore', 'Other']),
    'schools'       => clean($in['schools'] ?? '', 300),
    'intake'        => clean($in['intake'] ?? '', 20),
    'services'      => pick_many($in, 'services', ['GMAT coaching', 'Profile evaluation', 'B-school shortlisting', 'Essays / SOP', 'Interview preparation']),
    'notes'         => clean($in['notes'] ?? '', 3000),
];

$errors = [];
if ($p['name'] === '') $errors[] = 'Name is required.';
if (!filter_var($p['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if (!preg_match('/^\+?[0-9][0-9 ()\-]{6,24}$/', $p['phone'])) $errors[] = 'A valid phone number is required.';
foreach (['gmat_score', 'target_score'] as $k) {
    if ($p[$k] !== '' && (!ctype_digit($p[$k]) || (int)$p[$k] < 200 || (int)$p[$k] > 805)) {
        $errors[] = 'GMAT scores must be between 205 and 805.';
        break;
    }
}
if ($p['grad_year'] !== '' && !preg_match('/^(19|20)\d\d$/', $p['grad_year'])) $errors[] = 'Graduation year should look like 2022.';
if ($p['test_date'] !== '' && !preg_match('/^20\d\d-(0[1-9]|1[0-2])$/', $p['test_date'])) $p['test_date'] = '';
if (empty($in['consent'])) $errors[] = 'Please agree to be contacted.';
if ($errors) {
    json_out(422, ['ok' => false, 'error' => implode(' ', array_unique($errors))]);
}

$ip = client_ip();
$pdo = db();
$since = date('Y-m-d H:i:s', time() - 600);
$st = $pdo->prepare('SELECT SUM(email = ?), SUM(ip = ?) FROM profiles WHERE created_at >= ?');
$st->execute([$p['email'], $ip, $since]);
[$byEmail, $byIp] = array_map('intval', $st->fetch(PDO::FETCH_NUM) ?: [0, 0]);
if ($byEmail >= 3 || $byIp >= 10) {
    json_out(429, ['ok' => false, 'error' => 'Too many submissions. Please try again later or call 6301-696274.']);
}

$created = date('Y-m-d H:i:s');
$st = $pdo->prepare('INSERT INTO profiles (created_at, name, email, phone, city, data, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$st->execute([
    $created, $p['name'], $p['email'], $p['phone'], $p['city'],
    json_encode($p, JSON_UNESCAPED_UNICODE), $ip, clean($_SERVER['HTTP_USER_AGENT'] ?? '', 300),
]);
$id = (int)$pdo->lastInsertId();

notify_new_profile($p, $created);

json_out(200, ['ok' => true, 'id' => $id, 'message' => 'Thank you! Raaj will review your profile and get back to you.']);
