<?php
/**
 * <head> of every page.  Variables: $page_title, $stylesheets (extra files in frontend/css), $body_class
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> · AbsentEase</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(css_url('app.css')) ?>">
<?php foreach ($stylesheets ?? [] as $stylesheet): ?>
    <link rel="stylesheet" href="<?= e(css_url($stylesheet)) ?>">
<?php endforeach; ?>
</head>
<body class="<?= e($body_class ?? '') ?>">
