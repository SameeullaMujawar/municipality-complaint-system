<?php
require_once 'config/session.php';
session_destroy();
header('Location: /municipality-complaint-system/login.php');
exit;
