<?php
/*
Plugin Name: Memory Bank Monitor (Network)
Description: Monitora TSV sospetti in /memory-bank e invia alert su nuove registrazioni. Include comando WP-CLI.
Version: 1.0.0
Author: FAAC SecOps
*/

if (!defined('ABSPATH')) { exit; }

// Configurazione
const FAACSEC_MB_DIR = '/home/runcloud/webapps/FAAC/memory-bank/';
const FAACSEC_MB_LOG_DIR = '/home/runcloud/webapps/FAAC/memory-bank/alerts/';
const FAACSEC_MB_STATE_OPTION = 'faacsec_mb_monitor_state'; // network option

/**
 * Restituisce elenco TSV target da monitorare (sospetti)
 * @return array<string>
 */
function faacsec_mb_target_files(): array
{
    $patterns = [
        'suspicious_*.tsv',
        '*_suspicious*.tsv',
        'faac_at_suspicious*.tsv',
    ];
    $files = [];
    foreach ($patterns as $pat) {
        foreach (glob(FAACSEC_MB_DIR . $pat) ?: [] as $path) {
            if (is_file($path)) {
                $files[$path] = $path;
            }
        }
    }
    ksort($files);
    return array_values($files);
}

/**
 * Conta le righe dati (escludendo l'intestazione) di un TSV.
 */
function faacsec_mb_count_rows(string $file): int
{
    if (!is_readable($file)) { return 0; }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) { return 0; }
    return max(count($lines) - 1, 0);
}

/**
 * Esegue lo scan e produce un report testuale.
 * @return array{report:string, changes: array<string,int>}
 */
function faacsec_mb_run_scan(): array
{
    $state = get_site_option(FAACSEC_MB_STATE_OPTION, []);
    if (!is_array($state)) { $state = []; }

    $files = faacsec_mb_target_files();
    $lines = [];
    $changes = [];
    $now = current_time('mysql');
    $lines[] = "[FAACSEC] Memory-Bank scan at {$now}";

    foreach ($files as $file) {
        $count = faacsec_mb_count_rows($file);
        $prev  = isset($state[$file]) ? (int) $state[$file] : 0;
        $delta = $count - $prev;
        $changes[$file] = $delta;
        $basename = basename($file);
        $lines[] = sprintf('%s: rows=%d (Δ=%+d since last)', $basename, $count, $delta);
        // aggiorna stato
        $state[$file] = $count;
    }

    update_site_option(FAACSEC_MB_STATE_OPTION, $state);
    return [
        'report' => implode("\n", $lines) . "\n",
        'changes' => $changes,
    ];
}

/**
 * Scrive il report su file in alerts/ e su debug.log
 */
function faacsec_mb_write_report(string $report): ?string
{
    if (!is_dir(FAACSEC_MB_LOG_DIR)) {
        @mkdir(FAACSEC_MB_LOG_DIR, 0755, true);
    }
    $fname = FAACSEC_MB_LOG_DIR . 'monitor_report_' . gmdate('Ymd_His') . '.log';
    @file_put_contents($fname, $report);
    if (function_exists('error_log')) { error_log($report); }
    return $fname;
}

/**
 * Invia email con report se ci sono nuove righe.
 */
function faacsec_mb_maybe_email(array $changes, string $report, ?string $to = null): bool
{
    $has_new = false;
    foreach ($changes as $delta) { if ($delta > 0) { $has_new = true; break; } }
    if (!$has_new) { return false; }

    if (!$to) {
        $to = get_site_option('admin_email');
        if (!$to) { $to = get_option('admin_email'); }
    }
    $subject = '[FAACSEC] Nuove registrazioni sospette rilevate';
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    return wp_mail($to, $subject, $report, $headers);
}

// WP-CLI command: wp faacsec mb-scan [--email=addr]
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('faacsec mb-scan', function($args, $assoc_args) {
        $email = isset($assoc_args['email']) ? (string) $assoc_args['email'] : null;
        $result = faacsec_mb_run_scan();
        $file = faacsec_mb_write_report($result['report']);
        $sent = faacsec_mb_maybe_email($result['changes'], $result['report'], $email);
        WP_CLI::line($result['report']);
        WP_CLI::success('Report salvato in: ' . ($file ?: '(non scritto)'));
        WP_CLI::success('Email ' . ($sent ? 'inviata' : 'non inviata (nessun nuovo record)'));
    });
}


