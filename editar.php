<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';
$usuario = exigirLogin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$tarefa = R::findOne('tarefas', 'id = ? AND usuario_id = ?', [$id, $usuario['id']]);

if (!$tarefa || !$tarefa->id) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'Trabalho');
    $prioridade = $_POST['prioridade'] ?? 'Normal';
    $prazo = trim($_POST['prazo'] ?? '');

    if (mb_strlen($titulo) < 3) {
        $erro = 'Digite um título com pelo menos 3 caracteres.';
    } elseif (mb_strlen($titulo) > 180) {
        $erro = 'O título deve ter no máximo 180 caracteres.';
    } elseif ($prazo !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $prazo)) {
        $erro = 'Informe uma data válida.';
    } elseif (!in_array($prioridade, ['Baixa', 'Normal', 'Alta'], true)) {
        $erro = 'Selecione uma prioridade válida.';
    } else {
        $tarefa->titulo = $titulo;
        $tarefa->descricao = $descricao ?: null;
        $tarefa->categoria = $categoria ?: 'Trabalho';
        $tarefa->prioridade = $prioridade;
        $tarefa->prazo = $prazo ?: null;

        // O campo concluida não é alterado: o status atual é preservado.
        R::store($tarefa);
        header('Location: index.php?editada=1');
        exit;
    }
}

topo('Editar tarefa', 'editar');
?>
<section class="page">
    <div class="form-card">
        <span class="eyebrow">Atualizar registro</span>
        <h1>Editar tarefa</h1>
        <p class="intro">Modifique as informações e salve suas alterações.</p>

        <?php if ($erro): ?>
            <div class="message error"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="editar.php">
            <input type="hidden" name="id" value="<?= (int) $tarefa->id ?>">
            <div class="field-grid">
                <div class="field full">
                    <label for="titulo">Título</label>
                    <input id="titulo" name="titulo" required minlength="3" maxlength="180"
                           value="<?= e((string) $tarefa->titulo) ?>" autofocus>
                </div>

                <div class="field full">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao"><?= e((string) $tarefa->descricao) ?></textarea>
                </div>

                <div class="field">
                    <label for="prazo">Prazo</label>
                    <input id="prazo" name="prazo" type="date"
                           value="<?= e($tarefa->prazo ? (string) $tarefa->prazo : '') ?>">
                </div>

                <div class="field">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria">
                        <?php foreach (['Trabalho', 'Pessoal', 'Estudos', 'Casa'] as $opcao): ?>
                            <option value="<?= e($opcao) ?>" <?= $tarefa->categoria === $opcao ? 'selected' : '' ?>>
                                <?= e($opcao) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="prioridade">Prioridade</label>
                    <select id="prioridade" name="prioridade">
                        <?php foreach (['Baixa', 'Normal', 'Alta'] as $opcao): ?>
                            <option value="<?= e($opcao) ?>" <?= $tarefa->prioridade === $opcao ? 'selected' : '' ?>>
                                <?= e($opcao) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a class="secondary-button" href="index.php">Cancelar</a>
                <button class="primary-button" type="submit">Salvar alterações</button>
            </div>
        </form>
    </div>
</section>
<?php rodape(); ?>
