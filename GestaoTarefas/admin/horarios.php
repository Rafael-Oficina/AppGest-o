
<?php
include("config_bd.php");

// Eliminar horário
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $ligacao->prepare("DELETE FROM horarios WHERE id = ?");
    $stmt->execute([$id]);
    echo "<p class='mensagem-sucesso'>Horário eliminado com sucesso!</p>";
}

// Criar ou editar horário
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nome"]) && isset($_POST["hora_inicio"]) && isset($_POST["tempo_notificacao"])) {
    $nome = $_POST["nome"];
    $hora_inicio = $_POST["hora_inicio"];
    $tempo_notificacao = $_POST["tempo_notificacao"];
    $dias_semana = isset($_POST["dias_semana"]) ? implode(", ", $_POST["dias_semana"]) : "";

    if (!empty($_POST["id_horario"])) {
        $id = $_POST["id_horario"];
        $stmt = $ligacao->prepare("UPDATE horarios SET nome = ?, hora_inicio = ?, tempo_notificacao = ?, dias_semana = ? WHERE id = ?");
        $stmt->execute([$nome, $hora_inicio, $tempo_notificacao, $dias_semana, $id]);
        echo "<p class='mensagem-sucesso'>Horário atualizado com sucesso!</p>";
    } else {
        $stmt = $ligacao->prepare("INSERT INTO horarios (nome, hora_inicio, tempo_notificacao, dias_semana) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $hora_inicio, $tempo_notificacao, $dias_semana]);
        echo "<p class='mensagem-sucesso'>Horário criado com sucesso!</p>";
    }
}

// Buscar horário para edição
$horario_editar = null;
if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    $stmt = $ligacao->prepare("SELECT * FROM horarios WHERE id = ?");
    $stmt->execute([$id]);
    $horario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Buscar todos os horários
$todos_horarios = $ligacao->query("SELECT * FROM horarios ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestão de Horários</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Gestão de Horários</h1>

        <form method="post">
            <input type="hidden" name="id_horario" value="<?php echo $horario_editar ? $horario_editar['id'] : ''; ?>">
            <input class="campo" type="text" name="nome" placeholder="Nome do Horário" value="<?php echo $horario_editar ? $horario_editar['nome'] : ''; ?>" required>
            <input class="campo" type="time" name="hora_inicio" value="<?php echo $horario_editar ? $horario_editar['hora_inicio'] : ''; ?>" required>
            <input class="campo" type="number" name="tempo_notificacao" placeholder="Tempo para notificação" value="<?php echo $horario_editar ? $horario_editar['tempo_notificacao'] : ''; ?>" required>

            <label>Dias da Semana:</label><br>
            <?php
            $dias = ['Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'];
            $dias_selecionados = $horario_editar ? explode(", ", $horario_editar["dias_semana"]) : [];
            foreach ($dias as $dia) {
                $checked = in_array($dia, $dias_selecionados) ? 'checked' : '';
                echo "<label><input type='checkbox' name='dias_semana[]' value='$dia' $checked> $dia</label> ";
            }
            ?>
            <br><br>
            <button class="botao" type="submit"><?php echo $horario_editar ? "Guardar Alterações" : "Criar Horário"; ?></button>
        </form>

        <h2>Horários Existentes</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>Hora Início</th>
                <th>Tempo Notificação</th>
                <th>Dias da Semana</th>
                <th>Ações</th>
            </tr>
            <?php if (count($todos_horarios) > 0): ?>
                <?php foreach ($todos_horarios as $linha): ?>
                    <tr>
                        <td><?php echo $linha["nome"]; ?></td>
                        <td><?php echo $linha["hora_inicio"]; ?></td>
                        <td><?php echo $linha["tempo_notificacao"] ?? "-"; ?></td>
                        <td><?php echo $linha["dias_semana"]; ?></td>
                        <td>
                            <a href="?editar=<?php echo $linha['id']; ?>" class="botao" style="padding:6px 14px; font-size:0.9em;">Editar</a>
                            <a href="?eliminar=<?php echo $linha['id']; ?>" class="botao" style="padding:6px 14px; font-size:0.9em;" style="background-color:#c00;" onclick="return confirm('Eliminar este horário?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">Nenhum horário registado</td></tr>
            <?php endif; ?>
        </table>

        <br>
        <a href="painel.php"><button class="botao">Voltar</button></a>
    </div>
</body>
</html>
