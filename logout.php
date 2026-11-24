<?php
require_once __DIR__ . '/conexao.php';
encerrarSessao();
header('Location: login.php');
exit;
