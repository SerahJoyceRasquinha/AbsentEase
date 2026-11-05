<?php
/**
 * Connects the backend to the frontend/templates folder.
 *
 * A page controller collects data, then calls
 *     render_page('student/od_status', ['approved' => $rows, ...]);
 * which loads frontend/templates/student/od_status.view.php with those
 * variables available. Templates only DISPLAY data - no SQL, no logic.
 */

const TEMPLATES_DIR = PROJECT_ROOT . '/frontend/templates/';

function render_page(string $template, array $data = []): void
{
    $template_file = TEMPLATES_DIR . $template . '.view.php';
    if (!is_file($template_file)) {
        throw new RuntimeException("Template not found: $template_file");
    }

    extract($data, EXTR_SKIP);
    require $template_file;
}

/** Renders a small reusable piece, e.g. include_partial('close_panel_button'). */
function include_partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require TEMPLATES_DIR . 'partials/' . $name . '.view.php';
}

/** HTML-escape. Use for EVERY value printed in a template: <?= e($value) ?> */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** URL of a CSS file in frontend/css */
function css_url(string $file): string
{
    return app_url('frontend/css/' . $file);
}

/** URL of a JS file in frontend/js */
function js_url(string $file): string
{
    return app_url('frontend/js/' . $file);
}

/**
 * Small inline SVG icon, e.g. <?= icon('calendar') ?>. Outline style, inherits the text colour.
 */
function icon(string $name, string $class = 'icon'): string
{
    static $paths = [
        'home'      => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'list'      => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'inbox'     => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'layers'    => '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'x'         => '<path d="M18 6 6 18M6 6l12 12"/>',
        'external'  => '<path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'search'    => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'alert'     => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
        'menu'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'trash'     => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
        'cap'       => '<path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
        'refresh'   => '<path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/>',
        'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($paths[$name] ?? '') . '</svg>';
}

/** Status badge HTML: pending / approved / rejected */
function status_badge(string $status): string
{
    return '<span class="badge badge--' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

/** "2026-11-05 19:35:00" -> "05 Nov 2026, 7:35 PM" */
function format_datetime(?string $value): string
{
    return $value ? date('d M Y, g:i A', strtotime($value)) : '';
}
