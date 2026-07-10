<?php
/**
 * Portfolio OS — Admin About/Hero Save Controller
 * Handles POST from admin/about/index.php forms
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';

$adminUser = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/admin/about/');
    exit;
}
verifyCsrf();

$section = $_POST['section'] ?? '';

// ── HERO ──────────────────────────────────────────────────────
if ($section === 'hero') {
    $fullName    = trim($_POST['full_name'] ?? '');
    $title       = trim($_POST['title'] ?? '');
    $ctaPrimary  = trim($_POST['cta_primary'] ?? 'View Projects');
    $ctaSecondary= trim($_POST['cta_secondary'] ?? 'Contact Me');

    if ($fullName === '' || $title === '') {
        $_SESSION['flash_error'] = 'Full name and title are required.';
        header('Location: ' . APP_URL . '/admin/about/');
        exit;
    }

    // Process taglines (one per line → JSON array)
    $taglinesRaw = trim($_POST['taglines'] ?? '');
    $taglinesArr = array_values(array_filter(
        array_map('trim', explode("\n", $taglinesRaw))
    ));
    $taglinesJson = json_encode($taglinesArr);

    // Handle profile photo upload
    $existing    = Database::selectOne("SELECT profile_photo FROM hero_content WHERE id = 1");
    $photoFilename = $existing['profile_photo'] ?? null;

    if (!empty($_POST['remove_photo']) && $photoFilename) {
        $oldPath = UPLOAD_PATH . '/hero/' . $photoFilename;
        if (file_exists($oldPath)) unlink($oldPath);
        $photoFilename = null;
    }

    if (!empty($_FILES['profile_photo']['tmp_name'])) {
        if (!is_dir(UPLOAD_PATH . '/hero')) {
            mkdir(UPLOAD_PATH . '/hero', 0755, true);
        }
        $upload = processUpload(
            $_FILES['profile_photo'],
            UPLOAD_PATH . '/hero',
            ALLOWED_IMG_TYPES,
            3 * 1024 * 1024
        );
        if ($upload['success']) {
            if ($photoFilename) {
                $old = UPLOAD_PATH . '/hero/' . $photoFilename;
                if (file_exists($old)) unlink($old);
            }
            $photoFilename = $upload['filename'];
        } else {
            $_SESSION['flash_error'] = 'Photo upload failed: ' . $upload['error'];
            header('Location: ' . APP_URL . '/admin/about/');
            exit;
        }
    }

    // Upsert hero_content row 1
    $exists = Database::selectOne("SELECT id FROM hero_content WHERE id = 1");
    if ($exists) {
        Database::execute(
            "UPDATE hero_content SET full_name=?, title=?, taglines=?, cta_primary=?, cta_secondary=?, profile_photo=? WHERE id=1",
            [$fullName, $title, $taglinesJson, $ctaPrimary, $ctaSecondary, $photoFilename]
        );
    } else {
        Database::insert(
            "INSERT INTO hero_content (id, full_name, title, taglines, cta_primary, cta_secondary, profile_photo) VALUES (1,?,?,?,?,?,?)",
            [$fullName, $title, $taglinesJson, $ctaPrimary, $ctaSecondary, $photoFilename]
        );
    }

    logAuditEvent($adminUser['id'], 'hero.update', 'hero_content', 1);
    $_SESSION['flash_success'] = 'Hero section saved!';
    header('Location: ' . APP_URL . '/admin/about/');
    exit;
}

// ── ABOUT / BIO ───────────────────────────────────────────────
if ($section === 'about') {
    $bio         = trim($_POST['bio'] ?? '');
    $degree      = trim($_POST['education_degree'] ?? '');
    $institution = trim($_POST['education_institution'] ?? '');
    $years       = trim($_POST['education_years'] ?? '');

    // Build timeline JSON from parallel arrays
    $tYears  = $_POST['timeline_year']  ?? [];
    $tEvents = $_POST['timeline_event'] ?? [];
    $tDescs  = $_POST['timeline_desc']  ?? [];
    $timeline = [];
    foreach ($tYears as $i => $yr) {
        $yr  = trim($yr);
        $evt = trim($tEvents[$i] ?? '');
        if ($yr === '' && $evt === '') continue; // skip empty rows
        $timeline[] = [
            'year'        => $yr,
            'event'       => $evt,
            'description' => trim($tDescs[$i] ?? ''),
        ];
    }
    $timelineJson = json_encode($timeline);

    // Upsert about_content row 1
    $exists = Database::selectOne("SELECT id FROM about_content WHERE id = 1");
    if ($exists) {
        Database::execute(
            "UPDATE about_content SET bio=?, education_institution=?, education_degree=?, education_years=?, timeline_items=? WHERE id=1",
            [$bio, $institution, $degree, $years, $timelineJson]
        );
    } else {
        Database::insert(
            "INSERT INTO about_content (id, bio, education_institution, education_degree, education_years, timeline_items) VALUES (1,?,?,?,?,?)",
            [$bio, $institution, $degree, $years, $timelineJson]
        );
    }

    logAuditEvent($adminUser['id'], 'about.update', 'about_content', 1);
    $_SESSION['flash_success'] = 'Bio & Education saved!';
    header('Location: ' . APP_URL . '/admin/about/');
    exit;
}

$_SESSION['flash_error'] = 'Unknown section.';
header('Location: ' . APP_URL . '/admin/about/');
exit;
