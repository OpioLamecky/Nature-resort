<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
logout();
flash('You have been logged out.');
redirect(base_url('public/'));
