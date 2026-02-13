<?php
require __DIR__ . '/src/config.php';
session_destroy();
header('Location: /index.php');
