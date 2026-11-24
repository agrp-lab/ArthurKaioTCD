<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('gestao_tarefas_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $https,
        'samesite' => $https ? 'None' : 'Lax',
    ]);
    session_start();
}

function usuarioAtual(): ?array
{
    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    $usuario = R::load('usuarios', (int) $_SESSION['usuario_id']);
    if (!$usuario->id) {
        unset($_SESSION['usuario_id']);
        return null;
    }

    return [
        'id' => (int) $usuario->id,
        'nome' => (string) $usuario->nome,
        'email' => (string) $usuario->email,
    ];
}

function exigirLogin(): array
{
    $usuario = usuarioAtual();
    if (!$usuario) {
        header('Location: login.php');
        exit;
    }

    return $usuario;
}

function iniciarSessao(int $id): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $id;
}

function encerrarSessao(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function redirecionarSeLogado(): void
{
    if (usuarioAtual()) {
        header('Location: index.php');
        exit;
    }
}
