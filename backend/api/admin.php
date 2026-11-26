<?php
// https://gmatclap.com/api/admin.php - view, search, export and manage website enquiries.
declare(strict_types=1);
require __DIR__ . '/lib.php';

// One-time code needed only to set the first admin password.
const SETUP_CODE = '56CE8CC7';

header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

session_name('gcadmin');
session_set_cookie_params(['lifetime' => 0, 'path' => '/api/', 'secure' => (($_SERVER['HTTPS'] ?? 'off') !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),'httponly' => true, 'samesite' => 'Strict']);
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
$self = strtok((string)($_SERVER['REQUEST_URI'] ?? '/api/admin.php'), '?') ?: '/api/admin.php';
$post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
$action = (string)($_POST['action'] ?? $_GET['action'] ?? '');
$msg = '';
$err = '';
$view = (($_GET['view'] ?? $_POST['view'] ?? '') === 'profiles') ? 'profiles' : 'enquiries';
$table = $view; // whitelisted above: 'enquiries' or 'profiles'

function check_csrf(string $csrf): void
{
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Session expired. Go back and reload the page.');
    }
}

function too_many_attempts(string $ip): bool
{
    $pdo = db();
    $pdo->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([time() - 900]);
    $st = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?');
    $st->execute([$ip]);
    return (int)$st->fetchColumn() >= 8;
}

$hash = setting_get('admin_password_hash');
$loggedIn = !empty($_SESSION['admin']) && $hash !== null && hash_equals((string)$_SESSION['admin'], substr($hash, -16));

// ---------- first-time setup ----------
if ($hash === null && $post && $action === 'setup') {
    check_csrf($csrf);
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');
    if (!hash_equals(SETUP_CODE, strtoupper(trim((string)($_POST['code'] ?? ''))))) {
        $err = 'Setup code is not correct.';
    } elseif (strlen($p1) < 10) {
        $err = 'Use at least 10 characters.';
    } elseif ($p1 !== $p2) {
        $err = 'The two passwords do not match.';
    } else {
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        setting_set('admin_password_hash', $hash);
        session_regenerate_id(true);
        $_SESSION['admin'] = substr($hash, -16);
        header('Location: ' . $self);
        exit;
    }
}

// ---------- login / logout ----------
if ($hash !== null && $post && $action === 'login') {
    check_csrf($csrf);
    $ip = client_ip();
    if (too_many_attempts($ip)) {
        $err = 'Too many attempts. Wait 15 minutes and try again.';
    } elseif (password_verify((string)($_POST['password'] ?? ''), $hash)) {
        db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
        session_regenerate_id(true);
        $_SESSION['admin'] = substr($hash, -16);
        header('Location: ' . $self);
        exit;
    } else {
        db()->prepare('INSERT INTO login_attempts (ip, at) VALUES (?, ?)')->execute([$ip, time()]);
        sleep(1);
        $err = 'Wrong password.';
    }
}
if ($loggedIn && $post && $action === 'logout') {
    check_csrf($csrf);
    $_SESSION = [];
    session_destroy();
    header('Location: ' . $self);
    exit;
}

// ---------- actions when logged in ----------
if ($loggedIn) {
    if ($action === 'export') {
        if ($view === 'profiles') {
            $head = array_merge(['ID', 'Date (IST)'], array_values(PROFILE_FIELDS), ['Status']);
            $rows = [];
            foreach (db()->query('SELECT id, created_at, data, status FROM profiles ORDER BY id DESC') as $r) {
                $d = json_decode($r['data'], true) ?: [];
                $row = [$r['id'], $r['created_at']];
                foreach (array_keys(PROFILE_FIELDS) as $k) $row[] = $d[$k] ?? '';
                $row[] = $r['status'];
                $rows[] = $row;
            }
        } else {
            $head = ['ID', 'Date (IST)', 'Name', 'Email', 'Phone', 'City', 'Query', 'Page', 'Status'];
            $rows = db()->query('SELECT id, created_at, name, email, phone, city, queries, source, status FROM enquiries ORDER BY id DESC')->fetchAll();
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="gmatclap-' . $view . '-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // so Excel shows Indian names / symbols correctly
        fputcsv($out, $head);
        foreach ($rows as $r) {
            // Stop spreadsheet apps treating text as a formula.
            $r = array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r);
            fputcsv($out, $r);
        }
        exit;
    }
    if ($post && in_array($action, ['status', 'delete', 'password'], true)) {
        check_csrf($csrf);
        $id = (int)($_POST['id'] ?? 0);
        if ($action === 'status') {
            $status = in_array($_POST['status'] ?? '', ['new', 'contacted', 'enrolled', 'not interested'], true) ? $_POST['status'] : 'new';
            db()->prepare("UPDATE $table SET status = ? WHERE id = ?")->execute([$status, $id]);
            $msg = 'Status updated.';
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
            $msg = 'Deleted.';
        } elseif ($action === 'password') {
            $p1 = (string)($_POST['password'] ?? '');
            if (!password_verify((string)($_POST['current'] ?? ''), $hash)) {
                $err = 'Current password is wrong.';
            } elseif (strlen($p1) < 10 || $p1 !== (string)($_POST['password2'] ?? '')) {
                $err = 'New passwords must match and be at least 10 characters.';
            } else {
                $hash = password_hash($p1, PASSWORD_DEFAULT);
                setting_set('admin_password_hash', $hash);
                $_SESSION['admin'] = substr($hash, -16);
                $msg = 'Password changed.';
            }
        }
    }
}

// ---------- page ----------
function page_top(string $title): void
{
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="robots" content="noindex,nofollow"><title>' . h($title) . ' | GMATclap admin</title><link rel="icon" href="/logo.jpeg">';
    echo '<style>
    *{box-sizing:border-box}body{margin:0;font:15px/1.45 system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f6f6f7;color:#1d1d1f}
    header{background:#a41212;color:#fff;padding:12px 20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap}
    header h1{font-size:18px;margin:0;flex:1}header form{margin:0}
    main{max-width:1200px;margin:0 auto;padding:20px}
    .card{background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.08);padding:20px;margin-bottom:16px}
    .narrow{max-width:420px;margin:60px auto}
    label{display:block;font-weight:600;margin:12px 0 4px}
    input,select,textarea{font:inherit;padding:9px 10px;border:1px solid #ccc;border-radius:6px;width:100%}
    button,.btn{font:inherit;background:#a41212;color:#fff;border:0;border-radius:6px;padding:9px 16px;cursor:pointer;text-decoration:none;display:inline-block}
    button.light,.btn.light{background:#fff;color:#a41212;border:1px solid #a41212}
    button.small{padding:4px 10px;font-size:13px}
    .msg{background:#e8f5e9;color:#1b5e20;padding:10px 14px;border-radius:6px;margin-bottom:12px}
    .err{background:#fdecea;color:#8a1c12;padding:10px 14px;border-radius:6px;margin-bottom:12px}
    .bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:12px}.bar input,.bar select{width:auto;flex:1;min-width:160px}
    table{width:100%;border-collapse:collapse;background:#fff}th,td{text-align:left;padding:10px;border-bottom:1px solid #eee;vertical-align:top}
    th{font-size:13px;color:#666;background:#fafafa}td.q{max-width:340px;white-space:pre-wrap;word-break:break-word}
    .muted{color:#777;font-size:13px}.pill{font-size:12px;padding:2px 8px;border-radius:99px;background:#eee}
    .s-new{background:#fff3cd}.s-contacted{background:#d6e9ff}.s-enrolled{background:#d4edda}.s-not.interested{background:#eee}
    .stats{display:flex;gap:12px;flex-wrap:wrap}.stat{background:#fff;border-radius:10px;padding:12px 18px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
    .stat b{display:block;font-size:22px}
    .tablewrap{overflow-x:auto;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
    details summary{cursor:pointer;color:#a41212}
    .tabs{display:flex;gap:6px;margin-bottom:16px;border-bottom:2px solid #e5e5e5}
    .tab{padding:10px 16px;text-decoration:none;color:#555;font-weight:600;border-bottom:3px solid transparent;margin-bottom:-2px}
    .tab.on{color:#a41212;border-bottom-color:#a41212}
    .badge{background:#a41212;color:#fff;border-radius:99px;font-size:11px;padding:1px 7px;margin-left:4px}
    table.kv{margin-top:6px;font-size:13px}table.kv th{background:none;color:#666;font-weight:600;padding:3px 8px 3px 0;white-space:nowrap;border:0}table.kv td{padding:3px 0;border:0;white-space:normal}
    </style></head><body>';
}

if (!$loggedIn) {
    page_top($hash === null ? 'Set up' : 'Log in');
    echo '<main><div class="card narrow"><h2 style="margin-top:0">GMATclap admin</h2>';
    if ($err) echo '<div class="err">' . h($err) . '</div>';
    echo '<form method="post"><input type="hidden" name="csrf" value="' . h($csrf) . '">';
    if ($hash === null) {
        echo '<p class="muted">First-time setup. Choose the admin password you will use to see enquiries.</p>';
        echo '<input type="hidden" name="action" value="setup">';
        echo '<label>Setup code</label><input name="code" autocomplete="off" required>';
        echo '<label>New password (10+ characters)</label><input type="password" name="password" autocomplete="new-password" minlength="10" required>';
        echo '<label>Repeat password</label><input type="password" name="password2" autocomplete="new-password" minlength="10" required>';
        echo '<p><button>Save password</button></p>';
    } else {
        echo '<input type="hidden" name="action" value="login">';
        echo '<label>Password</label><input type="password" name="password" autocomplete="current-password" required autofocus>';
        echo '<p><button>Log in</button></p>';
    }
    echo '</form></div></main></body></html>';
    exit;
}

$q = clean($_GET['q'] ?? '', 100);
$fs = clean($_GET['status'] ?? '', 20);
$where = [];
$args = [];
if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ? OR ' . ($view === 'profiles' ? 'data' : 'queries') . ' LIKE ?)';
    array_push($args, ...array_fill(0, 5, '%' . $q . '%'));
}
if ($fs !== '') {
    $where[] = 'status = ?';
    $args[] = $fs;
}
$sql = "SELECT * FROM $table" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 500';
$st = db()->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

$counts = ['total' => 0, 'new' => 0, 'week' => 0];
$counts['total'] = (int)db()->query("SELECT COUNT(*) FROM $table")->fetchColumn();
$counts['new'] = (int)db()->query("SELECT COUNT(*) FROM $table WHERE status = 'new'")->fetchColumn();
$wk = db()->prepare("SELECT COUNT(*) FROM $table WHERE created_at >= ?");
$wk->execute([date('Y-m-d H:i:s', time() - 7 * 86400)]);
$counts['week'] = (int)$wk->fetchColumn();
$otherNew = (int)db()->query("SELECT COUNT(*) FROM " . ($view === 'profiles' ? 'enquiries' : 'profiles') . " WHERE status = 'new'")->fetchColumn();
$vq = $view === 'profiles' ? 'view=profiles&' : '';

$statuses = ['new', 'contacted', 'enrolled', 'not interested'];

page_top($view === 'profiles' ? 'Profile evaluations' : 'Enquiries');
echo '<header><h1>GMATclap admin</h1><a class="btn light" href="?' . $vq . 'action=export">Download spreadsheet (CSV)</a>';
echo '<form method="post"><input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="action" value="logout"><button class="light">Log out</button></form></header><main>';
$tab = fn(string $v, string $label) => '<a class="tab' . ($view === $v ? ' on' : '') . '" href="' . h($self) . ($v === 'profiles' ? '?view=profiles' : '') . '">' . $label . ($view !== $v && $otherNew > 0 ? ' <span class="badge">' . $otherNew . ' new</span>' : '') . '</a>';
echo '<nav class="tabs">' . $tab('enquiries', 'Enquiries') . $tab('profiles', 'Profile evaluations') . '</nav>';
if ($msg) echo '<div class="msg">' . h($msg) . '</div>';
if ($err) echo '<div class="err">' . h($err) . '</div>';
echo '<div class="stats" style="margin-bottom:16px"><div class="stat"><b>' . $counts['total'] . '</b>total</div><div class="stat"><b>' . $counts['new'] . '</b>new (not contacted)</div><div class="stat"><b>' . $counts['week'] . '</b>last 7 days</div></div>';

echo '<form class="bar" method="get">' . ($view === 'profiles' ? '<input type="hidden" name="view" value="profiles">' : '') . '<input name="q" placeholder="Search name, email, phone, city, query" value="' . h($q) . '"><select name="status"><option value="">All statuses</option>';
foreach ($statuses as $s) echo '<option' . ($fs === $s ? ' selected' : '') . '>' . h($s) . '</option>';
echo '</select><button>Search</button>' . ($q !== '' || $fs !== '' ? ' <a href="' . h($self) . ($view === 'profiles' ? '?view=profiles' : '') . '">Clear</a>' : '') . '</form>';

if (!$rows) {
    echo '<div class="card">No enquiries ' . ($q !== '' || $fs !== '' ? 'match this search.' : 'yet. ' . ($view === 'profiles' ? 'Requests from the Free Profile Evaluation page will appear here.' : 'New ones from the Enquiry and Contact Us forms will appear here.')) . '</div>';
} else {
    echo '<div class="tablewrap"><table><thead><tr><th>Date (IST)</th><th>Name</th><th>Contact</th><th>City</th><th>' . ($view === 'profiles' ? 'Profile' : 'Query') . '</th><th>Status</th><th></th></tr></thead><tbody>';
    foreach ($rows as $r) {
        $tel = preg_replace('/[^0-9+]/', '', $r['phone']);
        $wa = preg_replace('/[^0-9]/', '', $r['phone']);
        if (strlen($wa) === 10) $wa = '91' . $wa;
        echo '<tr>';
        echo '<td>' . h(date('d M Y', strtotime($r['created_at']))) . '<div class="muted">' . h(date('g:i a', strtotime($r['created_at']))) . '</div></td>';
        echo '<td><b>' . h($r['name']) . '</b>' . (($r['source'] ?? '') !== '' ? '<div class="muted">from ' . h($r['source']) . '</div>' : '') . '</td>';
        echo '<td><a href="mailto:' . h($r['email']) . '">' . h($r['email']) . '</a><br><a href="tel:' . h($tel) . '">' . h($r['phone']) . '</a> · <a href="https://wa.me/' . h($wa) . '" target="_blank" rel="noopener">WhatsApp</a></td>';
        echo '<td>' . h($r['city']) . '</td>';
        if ($view === 'profiles') {
            $d = json_decode($r['data'], true) ?: [];
            $sum = array_filter([
                trim(($d['degree'] ?? '') . (($d['college'] ?? '') !== '' ? ', ' . $d['college'] : '')),
                ($d['work_years'] ?? '') !== '' ? $d['work_years'] . ' yrs exp' . (($d['industry'] ?? '') !== '' ? ' · ' . $d['industry'] : '') : '',
                ($d['gmat_status'] ?? '') !== '' ? 'GMAT: ' . $d['gmat_status'] . (($d['gmat_score'] ?? '') !== '' ? ' ' . $d['gmat_score'] : '') . (($d['target_score'] ?? '') !== '' ? ' (target ' . $d['target_score'] . ')' : '') : '',
                $d['programs'] ?? '',
                ($d['intake'] ?? '') !== '' ? 'Intake ' . $d['intake'] : '',
            ], fn($x) => $x !== '');
            echo '<td class="q">' . h(implode("\n", $sum)) . '<details><summary>All details</summary><table class="kv">';
            foreach (PROFILE_FIELDS as $k => $label) {
                if (($d[$k] ?? '') !== '' && !in_array($k, ['name', 'email', 'phone', 'city'], true)) {
                    echo '<tr><th>' . h($label) . '</th><td>' . h((string)$d[$k]) . '</td></tr>';
                }
            }
            echo '</table></details></td>';
        } else {
            echo '<td class="q">' . h($r['queries']) . '</td>';
        }
        echo '<td><form method="post"><input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="view" value="' . $view . '"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="' . (int)$r['id'] . '">';
        echo '<select name="status" onchange="this.form.submit()" class="s-' . h(str_replace(' ', '.', $r['status'])) . '">';
        foreach ($statuses as $s) echo '<option' . ($r['status'] === $s ? ' selected' : '') . '>' . h($s) . '</option>';
        echo '</select></form></td>';
        echo '<td><form method="post" onsubmit="return confirm(\'Delete this permanently?\')"><input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="view" value="' . $view . '"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . (int)$r['id'] . '"><button class="small light">Delete</button></form></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    if (count($rows) === 500) echo '<p class="muted">Showing the latest 500. Download the spreadsheet for all of them.</p>';
}

echo '<div class="card" style="margin-top:24px"><details><summary>Change admin password</summary><form method="post" style="max-width:380px">';
echo '<input type="hidden" name="csrf" value="' . h($csrf) . '"><input type="hidden" name="action" value="password">';
echo '<label>Current password</label><input type="password" name="current" autocomplete="current-password" required>';
echo '<label>New password (10+ characters)</label><input type="password" name="password" autocomplete="new-password" minlength="10" required>';
echo '<label>Repeat new password</label><input type="password" name="password2" autocomplete="new-password" minlength="10" required>';
echo '<p><button>Change password</button></p></form></details></div>';
echo '</main></body></html>';
