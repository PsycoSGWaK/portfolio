<?php

/**
 * Endpoint de déploiement HTTPS pour o2switch (SSH entrant bloqué par le pare-feu).
 *
 * Reçoit un artefact déjà construit par GitHub Actions (tar.gz : code + vendor +
 * assets compilés), l'extrait dans un dossier de release neuf, puis lance
 * deploy/finalize.sh (link shared -> migrate -> bascule du symlink `current`).
 *
 * VOLONTAIREMENT sans aucune dépendance Symfony : ce fichier doit rester
 * fonctionnel même si l'application est en erreur. Le seul rempart est le token
 * partagé (Bearer), stocké côté serveur dans shared/deploy-hook.secret et jamais
 * commité. Le pendant côté CI est le secret GitHub DEPLOY_HOOK_TOKEN.
 */

declare(strict_types=1);

// Base de déploiement, indépendante du docroot qui sert ce fichier.
// Ajuster si le home o2switch change (cf. compte cPanel nayo1552).
const DEPLOY_BASE = '/home/nayo1552/deploy/guillaumehurard';

header('Content-Type: text/plain; charset=utf-8');

function fail(int $code, string $msg): void
{
    http_response_code($code);
    echo $msg . "\n";
    exit;
}

// --- 1. Méthode --------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    fail(405, 'Method Not Allowed');
}

// --- 2. Authentification (token Bearer, comparaison à temps constant) --------
$secretFile = DEPLOY_BASE . '/shared/deploy-hook.secret';
if (!is_readable($secretFile)) {
    fail(500, 'Deploy secret not configured on server');
}
$expected = trim((string) file_get_contents($secretFile));

$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
$provided = '';
if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m) === 1) {
    $provided = trim($m[1]);
}
if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
    fail(401, 'Unauthorized');
}

// --- 3. Verrou anti-concurrence ---------------------------------------------
$lock = fopen(DEPLOY_BASE . '/shared/deploy.lock', 'c');
if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
    fail(409, 'A deployment is already running');
}

// --- 4. Artefact reçu --------------------------------------------------------
if (!isset($_FILES['artifact']) || $_FILES['artifact']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['artifact']['error'] ?? 'absent';
    fail(400, "Missing or invalid artifact upload (error: {$err})");
}
$tmp = $_FILES['artifact']['tmp_name'];

// --- 5. Dossier de release neuf ---------------------------------------------
$release = DEPLOY_BASE . '/releases/' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
if (!mkdir($release, 0755, true) && !is_dir($release)) {
    fail(500, 'Cannot create release directory');
}

// --- 6. Extraction (archive de confiance : émise par le CI authentifié) ------
exec(sprintf('tar -xzf %s -C %s 2>&1', escapeshellarg($tmp), escapeshellarg($release)), $out, $rc);
if ($rc !== 0) {
    exec('rm -rf ' . escapeshellarg($release));
    fail(500, "Extraction failed:\n" . implode("\n", $out));
}

// --- 7. Finalisation (link shared -> migrate -> bascule symlink) -------------
@set_time_limit(0);
$finalize = $release . '/deploy/finalize.sh';
if (!is_file($finalize)) {
    exec('rm -rf ' . escapeshellarg($release));
    fail(500, 'finalize.sh missing from artifact');
}
exec(sprintf('bash %s 2>&1', escapeshellarg($finalize)), $flog, $frc);
echo implode("\n", $flog) . "\n";
if ($frc !== 0) {
    fail(500, "Finalize failed (exit {$frc})");
}

echo 'OK deployed: ' . basename($release) . "\n";
