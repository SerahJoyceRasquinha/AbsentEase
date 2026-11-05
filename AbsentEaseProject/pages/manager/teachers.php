<?php
/**
 * ADMIN -> "Teachers"
 * Add teachers so they can sign up, edit username/email, delete.
 * Deleting removes their login and class setup (database cascade); their class's
 * pending OD requests wait for the next class teacher.
 * Template: frontend/templates/manager/teachers.view.php
 */
require __DIR__ . '/../../backend/bootstrap.php';
require_login('manager');

if (posted('add_teacher'))    { flash_result(add_teacher(post('username'), post('email'))); reload_page(); }
if (posted('update_teacher')) { flash_result(update_teacher(post('old_username'), post('username'), post('email'))); reload_page(); }
if (posted('delete_teacher')) { flash_result(remove_teacher(post('username'))); reload_page(); }

render_page('manager/teachers', ['teachers' => teachers_all()]);
