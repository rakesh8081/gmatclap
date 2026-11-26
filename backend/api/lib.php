<?php
// Shared helpers for the gmatclap.com API. Not meant to be opened directly.
declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

const NOTIFY_EMAIL = 'gmatclap@gmail.com';
const MAIL_FROM    = 'website@gmatclap.com';
const SITE_NAME    = 'GMATclap';

// Data lives one level above public_html, so it can never be downloaded from the web.
// public_html/api/lib.php -> /home/<user>/domains/gmatclap.com/gmatclap-data
function data_dir(): string
{
    $dir = dirname(__DIR__, 2) . '/gmatclap-data';
    if (!is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    return $dir;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $pdo = new PDO('sqlite:' . data_dir() . '/site.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec("CREATE TABLE IF NOT EXISTS enquiries (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        created_at  TEXT NOT NULL,
        name        TEXT NOT NULL,
        email       TEXT NOT NULL,
        phone       TEXT NOT NULL,
        city        TEXT,
        queries     TEXT,
        source      TEXT,
        ip          TEXT,
        user_agent  TEXT,
        status      TEXT NOT NULL DEFAULT 'new'
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (ip TEXT, at INTEGER)");
    return $pdo;
}

function setting_get(string $k): ?string
{
    $st = db()->prepare('SELECT v FROM settings WHERE k = ?');
    $st->execute([$k]);
    $v = $st->fetchColumn();
    return $v === false ? null : (string)$v;
}

function setting_set(string $k, string $v): void
{
    $st = db()->prepare('INSERT INTO settings (k, v) VALUES (?, ?) ON CONFLICT(k) DO UPDATE SET v = excluded.v');
    $st->execute([$k, $v]);
}

function client_ip(): string
{
    // Behind Hostinger's CDN the visitor's address is the first one in X-Forwarded-For.
    $xff = trim(explode(',', (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]);
    $ip = filter_var($xff, FILTER_VALIDATE_IP) ? $xff : (string)($_SERVER['REMOTE_ADDR'] ?? '');
    return substr($ip, 0, 45);
}

function clean(mixed $v, int $max): string
{
    $s = is_scalar($v) ? (string)$v : '';
    $s = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s) ?? '');
    return mb_substr($s, 0, $max);
}

function h(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_out(int $code, array $body): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function notify_new_enquiry(array $e): void
{
    $subject = 'New enquiry from ' . $e['name'] . ' (' . SITE_NAME . ' website)';
    $lines = [
        'A new enquiry was submitted on gmatclap.com.',
        '',
        'Name:    ' . $e['name'],
        'Email:   ' . $e['email'],
        'Phone:   ' . $e['phone'],
        'City:    ' . $e['city'],
        'Page:    ' . $e['source'],
        'Time:    ' . $e['created_at'] . ' IST',
        '',
        'Query:',
        $e['queries'] !== '' ? $e['queries'] : '(none)',
        '',
        'All enquiries: https://gmatclap.com/api/admin.php',
    ];
    $headers = [
        'From: ' . SITE_NAME . ' Website <' . MAIL_FROM . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    if (filter_var($e['email'], FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $e['email'];
    }
    // Best effort: the enquiry is already saved even if the email fails.
    @mail(NOTIFY_EMAIL, '=?UTF-8?B?' . base64_encode($subject) . '?=', implode("\r\n", $lines), implode("\r\n", $headers));
}
