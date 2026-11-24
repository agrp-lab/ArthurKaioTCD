<?php
require_once __DIR__ . '/conexao.php';
redirecionarSeLogado();

$erro = '';
$nome = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $confirmacao = (string) ($_POST['confirmacao'] ?? '');

    if (mb_strlen($nome) < 2) {
        $erro = 'Digite seu nome.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } elseif (mb_strlen($senha) < 8) {
        $erro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } elseif (R::count('usuarios', 'email = ?', [$email]) > 0) {
        $erro = 'Já existe uma conta com este e-mail.';
    } else {
        $haviaUsuarios = R::count('usuarios') > 0;
        $usuario = R::dispense('usuarios');
        $usuario->nome = $nome;
        $usuario->email = $email;
        $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);
        $id = R::store($usuario);

        // Preserva tarefas criadas antes da autenticação na primeira conta.
        if (!$haviaUsuarios) {
            R::exec('UPDATE tarefas SET usuario_id = ? WHERE usuario_id IS NULL', [$id]);
        }

        iniciarSessao((int) $id);
        header('Location: index.php');
        exit;
    }
}

topo('Criar conta', 'cadastro');
?>
<section class="auth-page">
    <div class="auth-card">
        <div class="auth-mark">≡</div>
        <span class="eyebrow">Comece agora</span>
        <h1>Criar sua conta</h1>
        <p class="intro">Sua lista fica privada e separada das outras contas.</p>
        <?php if ($erro): ?><div class="message error"><?= e($erro) ?></div><?php endif; ?>
        <form method="post" class="auth-form">
            <div class="field"><label for="nome">Nome</label><input id="nome" name="nome" value="<?= e($nome) ?>" required maxlength="100" autocomplete="name"></div>
            <div class="field"><label for="email">E-mail</label><input id="email" name="email" type="email" value="<?= e($email) ?>" required autocomplete="email"></div>
            <div class="field"><label for="senha">Senha</label><input id="senha" name="senha" type="password" required minlength="8" autocomplete="new-password"><small class="field-hint">Mínimo de 8 caracteres.</small></div>
            <div class="field"><label for="confirmacao">Confirmar senha</label><input id="confirmacao" name="confirmacao" type="password" required minlength="8" autocomplete="new-password"></div>
            <button class="primary-button" type="submit">Criar conta</button>
        </form>
        <p class="auth-switch">Já tem uma conta? <a href="login.php">Entrar</a></p>
    </div>
</section>
<?php rodape(); ?>
