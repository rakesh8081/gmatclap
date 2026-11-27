<?php
// POST /api/enquiry.php  - saves an enquiry from the website forms and emails a notification.
// Accepts JSON (what the Angular forms send) or a normal form post.
declare(strict_types=1);
require __DIR__ . '/lib.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && isset($_GET['health'])) {
    try {
        db()->query('SELECT 1');
        json_out(200, ['ok' => true]);
    } catch (Throwable $e) {
        json_out(500, ['ok' => false, 'error' => 'storage unavailable']);
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    json_out(405, ['ok' => false, 'error' => 'Use POST']);
}

$raw = file_get_contents('php://input', false, null, 0, 20000) ?: '';
$in = json_decode($raw, true);
if (!is_array($in)) {
    $in = $_POST;
}

// Hidden "website" field: real visitors leave it empty, simple spam bots fill it in.
if (clean($in['website'] ?? '', 200) !== '') {
    json_out(200, ['ok' => true]);
}

$e = [
    'name'    => clean($in['name'] ?? '', 120),
    'email'   => clean($in['email'] ?? '', 160),
    'phone'   => clean($in['phone'] ?? '', 30),
    'city'    => clean($in['city'] ?? '', 80),
    'queries' => clean($in['queries'] ?? ($in['message'] ?? ''), 3000),
];

$errors = [];
if ($e['name'] === '') {
    $errors[] = 'Name is required.';
}
if (!filter_var($e['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}
if (!preg_match('/^\+?[0-9][0-9 ()\-]{6,24}$/', $e['phone'])) {
    $errors[] = 'A valid phone number is required.';
}
if ($errors) {
    json_out(422, ['ok' => false, 'error' => implode(' ', $errors)]);
}

$ip = client_ip();
$pdo = db();

// Flood protection: at most 3 per email and 10 per address in 10 minutes.
$since = date('Y-m-d H:i:s', time() - 600);
$st = $pdo->prepare("SELECT SUM(email = ?), SUM(ip = ?) FROM enquiries WHERE created_at >= ?");
$st->execute([$e['email'], $ip, $since]);
[$byEmail, $byIp] = array_map('intval', $st->fetch(PDO::FETCH_NUM) ?: [0, 0]);
if ($byEmail >= 3 || $byIp >= 10) {
    json_out(429, ['ok' => false, 'error' => 'Too many enquiries. Please try again later or call 6301-696274.']);
}

$ref = (string)($_SERVER['HTTP_REFERER'] ?? '');
$e['source'] = clean(parse_url($ref, PHP_URL_PATH) ?: ($in['source'] ?? ''), 200);
$e['created_at'] = date('Y-m-d H:i:s');

$st = $pdo->prepare('INSERT INTO enquiries (created_at, name, email, phone, city, queries, source, ip, user_agent)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$st->execute([
    $e['created_at'], $e['name'], $e['email'], $e['phone'], $e['city'], $e['queries'], $e['source'],
    $ip, clean($_SERVER['HTTP_USER_AGENT'] ?? '', 300),
]);
$id = (int)$pdo->lastInsertId();

notify_new_enquiry($e);

json_out(200, ['ok' => true, 'id' => $id, 'message' => 'Thank you! We will get back to you soon.']);
