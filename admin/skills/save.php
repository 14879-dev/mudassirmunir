<?php
/**
 * Portfolio OS — Admin Skills: Save (skill OR language)
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

$adminUser = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/skills/');
    exit;
}
verifyCsrf();

$type = $_POST['type'] ?? '';

if ($type === 'skill') {
    $name        = trim($_POST['name'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $proficiency = max(0, min(100, (int)($_POST['proficiency'] ?? 75)));
    $icon        = trim($_POST['icon'] ?? '');

    if ($name === '' || $category === '') {
        $_SESSION['flash_error'] = 'Name and category are required.';
        header('Location: ' . APP_URL . '/admin/skills/');
        exit;
    }

    $maxOrder = Database::selectOne("SELECT MAX(sort_order) as m FROM skills")['m'] ?? 0;
    Database::insert(
        "INSERT INTO skills (name, category, proficiency, icon, sort_order) VALUES (?,?,?,?,?)",
        [$name, $category, $proficiency, $icon, (int)$maxOrder + 1]
    );
    logAuditEvent($adminUser['id'], 'skill.create', 'skills', 0);
    $_SESSION['flash_success'] = "Skill \"$name\" added.";

} elseif ($type === 'language') {
    $name  = trim($_POST['name'] ?? '');
    $ltype = in_array($_POST['lang_type'] ?? '', ['spoken','programming']) ? $_POST['lang_type'] : 'spoken';
    $level = in_array($_POST['proficiency_level'] ?? '', ['Native','Advanced','Intermediate','Elementary'])
             ? $_POST['proficiency_level'] : 'Intermediate';

    if ($name === '') {
        $_SESSION['flash_error'] = 'Name is required.';
        header('Location: ' . APP_URL . '/admin/skills/');
        exit;
    }

    $maxOrder = Database::selectOne("SELECT MAX(sort_order) as m FROM languages")['m'] ?? 0;
    Database::insert(
        "INSERT INTO languages (name, lang_type, proficiency_level, sort_order) VALUES (?,?,?,?)",
        [$name, $ltype, $level, (int)$maxOrder + 1]
    );
    logAuditEvent($adminUser['id'], 'language.create', 'languages', 0);
    $_SESSION['flash_success'] = "Language \"$name\" added.";

} else {
    $_SESSION['flash_error'] = 'Invalid type.';
}

header('Location: ' . APP_URL . '/admin/skills/');
exit;
