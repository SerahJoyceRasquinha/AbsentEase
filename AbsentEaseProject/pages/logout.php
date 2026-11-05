<?php
/**
 * LOGOUT for every role.
 */
require __DIR__ . '/../backend/bootstrap.php';

logout_everyone();
flash('You have been logged out.', 'info');
redirect_to('index.php');
