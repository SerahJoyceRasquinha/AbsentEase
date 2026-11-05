<?php
/**
 * Small helpers for requests, URLs, redirects and pop-up messages.
 */

/** True when the current request is a form submission. */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Trimmed POST value ('' if missing). */
function post(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

/** Trimmed value from the URL query string ('' if missing). */
function query_param(string $key): string
{
    return trim((string)($_GET[$key] ?? ''));
}

/** Was this submit button pressed? (e.g. <button name="teacher_login">) */
function posted(string $button): bool
{
    return is_post() && isset($_POST[$button]);
}

/**
 * Absolute URL of a file inside the project, e.g.
 *     app_url('frontend/css/common.css')  ->  /AbsentEase/frontend/css/common.css
 * Works no matter which folder of htdocs the project sits in.
 */
function app_url(string $path_from_project_root = ''): string
{
    static $base = null;

    if ($base === null) {
        // Path of the running script relative to the project root, e.g. "/pages/student/od_status.php"
        $script_file  = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME']));
        $project_root = str_replace('\\', '/', realpath(PROJECT_ROOT));
        $relative     = substr($script_file, strlen($project_root));

        // Strip that from the URL the browser used -> URL of the project root.
        $script_url = $_SERVER['SCRIPT_NAME'];
        $base = rtrim(substr($script_url, 0, strlen($script_url) - strlen($relative)), '/');
    }
    return $base . '/' . ltrim($path_from_project_root, '/');
}

/** Redirect and stop. */
function redirect_to(string $path_from_project_root): void
{
    header('Location: ' . app_url($path_from_project_root));
    exit;
}

/** Reload the current page (same URL, incl. ?tab=...) with a GET - stops "resubmit form?" on refresh. */
function reload_page(): void
{
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

/**
 * Queue a message to show on the next page that renders (survives redirects).
 *   $type:   'success' | 'error' | 'info'
 *   $dialog: true = centred dialog box the user must close (e.g. "OD approved"),
 *            false = small notification in the corner that fades away
 *   $note:   small grey text under a dialog's message
 */
function flash(string $message, string $type = 'success', ?string $title = null, bool $dialog = false, ?string $note = null): void
{
    $_SESSION['flash_messages'][] = ['message' => $message, 'type' => $type, 'title' => $title, 'dialog' => $dialog, 'note' => $note];
}

/** flash() for the [type, message] pairs returned by the *_service.php functions. */
function flash_result(array $result): void
{
    flash($result[1], $result[0]);
}

/** Returns and clears the queued messages (used by the page footer). */
function take_flash_messages(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}
