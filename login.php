<?php
require_once __DIR__ . '/conexao.php';
redirecionarSeLogado();

$erro = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $usuario = R::findOne('usuarios', ' email = ? ', [$email]);

    if (!$usuario || !password_verify($senha, (string) $usuario->senha)) {
        $erro = 'E-mail ou senha inválidos.';
    } else {
        iniciarSessao((int) $usuario->id);
        header('Location: index.php');
        exit;
    }
}

topo('Entrar', 'login');
?>
<section class="auth-page">
    <div class="auth-card">
        <div class="auth-mark">≡</div>
        <span class="eyebrow">Gestão pessoal</span>
        <h1>Entrar na sua lista</h1>
        <p class="intro">Acesse suas tarefas de qualquer lugar.</p>
        <?php if ($erro): ?><div class="message error"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" class="auth-form">
            <div class="field"><label for="email">E-mail</label><input id="email" name="email" type="email" value="<?= e($email) ?>" required autofocus autocomplete="email"></div>
            <div class="field"><label for="senha">Senha</label><input id="senha" name="senha" type="password" required autocomplete="current-password"></div>
            <button class="primary-button" type="submit">Entrar</button>
        </form>
        <p class="auth-switch">Ainda não tem uma conta? <a href="cadastro.php">Criar conta</a></p>
    </div>
</section>
<?php rodape(); ?>
