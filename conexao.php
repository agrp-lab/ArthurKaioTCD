<?php
declare(strict_types=1);

require_once __DIR__ . '/class/rb.php';
require_once __DIR__ . '/auth.php';

// Hospedagens sem variáveis de ambiente (ex.: InfinityFree) usam o config.php.
$cfg = file_exists(__DIR__ . '/config.php') ? (require __DIR__ . '/config.php') : [];

$dsn = getenv('DB_DSN');
if ($dsn) {
    R::setup($dsn, getenv('DB_USER') ?: null, getenv('DB_PASS') ?: null);
} else {
    $host = $cfg['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1');
    $port = $cfg['port'] ?? (getenv('DB_PORT') ?: '3306');
    $name = $cfg['name'] ?? (getenv('DB_NAME') ?: 'gestao_tarefas');
    $user = $cfg['user'] ?? (getenv('DB_USER') ?: 'root');
    $pass = $cfg['pass'] ?? (getenv('DB_PASS') ?: '');
    R::setup("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass);
}

R::exec('CREATE TABLE IF NOT EXISTS usuarios (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, senha VARCHAR(255) NOT NULL, criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
R::exec('CREATE TABLE IF NOT EXISTS tarefas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NULL, titulo VARCHAR(180) NOT NULL, descricao TEXT NULL, categoria VARCHAR(80) NOT NULL DEFAULT "Trabalho", prioridade ENUM("Baixa", "Normal", "Alta") NOT NULL DEFAULT "Normal", prazo DATE NULL, concluida TINYINT(1) NOT NULL DEFAULT 0, criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP, atualizada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_tarefas_usuario (usuario_id), INDEX idx_tarefas_status (concluida), INDEX idx_tarefas_prazo (prazo)) ENGINE=InnoDB');

try {
    R::exec('ALTER TABLE tarefas ADD COLUMN usuario_id INT UNSIGNED NULL AFTER id');
} catch (Throwable $e) {
    // A coluna já existe em instalações atualizadas.
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function topo(string $titulo, string $pagina): void
{
    $usuario = usuarioAtual();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($titulo) ?> · Tarefas</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/auth.css">
    </head>
    <body>
        <main class="content">
            <header class="topbar">
                <a class="brand" href="index.php">
                    <span class="brand-mark">≡</span>
                    <span>Tarefas</span>
                </a>
                <span class="location">/ <?= e($titulo) ?></span>
                <?php if ($usuario): ?>
                    <div class="account-menu">
                        <span><?= e($usuario['nome']) ?></span>
                        <a href="logout.php">Sair</a>
                    </div>
                <?php endif; ?>
            </header>
    <?php
}

function rodape(): void
{
    ?>
        </main>
    </body>
    </html>
    <?php
}
