
<?php
session_start();
if (!isset($_SESSION["admin_logado"])) {
    header("Location: login.php");
    exit();
}

require_once "config_bd.php";

$mensagem = "";
$erro = "";

// Eliminar
if (isset($_GET["eliminar"])) {
    $id = (int) $_GET["eliminar"];
    try {
        $stmt = $ligacao->prepare("DELETE FROM funcionarios WHERE id = ?");
        $stmt->execute([$id]);
        $mensagem = "Funcionário eliminado com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao eliminar: " . $e->getMessage();
    }
}

// Editar
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["editar_id"])) {
    $id = (int) $_POST["editar_id"];
    $numero = trim($_POST["numero"]);
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $horario = trim($_POST["horario"]);
    $relatorio = trim($_POST["relatorio"]);
    $utilizador = trim($_POST["utilizador"]);
    $password = trim($_POST["password"]);

    if ($numero && $nome && $utilizador && $password) {
        try {
            $stmt = $ligacao->prepare("UPDATE funcionarios SET numero=?, nome=?, email=?, horario_id=?, relatorio_acesso=?, utilizador=?, password=? WHERE id=?");
            $stmt->execute([$numero, $nome, $email ?: null, $horario ?: null, $relatorio, $utilizador, $password, $id]);
            $mensagem = "Funcionário atualizado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha os campos obrigatórios.";
    }
}

// Inserir novo
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["criar"])) {
    $numero = trim($_POST["numero"]);
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $horario = trim($_POST["horario"]);
    $relatorio = trim($_POST["relatorio"]);
    $utilizador = trim($_POST["utilizador"]);
    $password = trim($_POST["password"]);

    if ($numero && $nome && $utilizador && $password) {
        try {
            $stmt = $ligacao->prepare("INSERT INTO funcionarios (numero, nome, email, horario_id, relatorio_acesso, utilizador, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$numero, $nome, $email ?: null, $horario ?: null, $relatorio, $utilizador, $password]);
            $mensagem = "Funcionário criado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao criar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha os campos obrigatórios.";
    }
}

$horarios = [];
try {
    $result = $ligacao->query("SELECT id, nome FROM horarios ORDER BY nome");
    $horarios = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$funcionarios = [];
try {
    $result = $ligacao->query("SELECT f.*, h.nome as horario_nome FROM funcionarios f LEFT JOIN horarios h ON f.horario_id = h.id ORDER BY f.numero");
    $funcionarios = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

$editar_dados = null;
if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    foreach ($funcionarios as $f) {
        if ($f["id"] == $id) {
            $editar_dados = $f;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Funcionários</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body style="overflow-y: auto; height: 100vh; margin: 0; padding: 0;">
<div class="container" style="margin-top: 0;">
    <h1>Gestão de Funcionários</h1>
    <?php if ($mensagem): ?><p style="color:green;"><?php echo $mensagem; ?></p><?php endif; ?>
    <?php if ($erro): ?><p style="color:red;"><?php echo $erro; ?></p><?php endif; ?>

    <form method="post">
        <?php if ($editar_dados): ?>
            <input type="hidden" name="editar_id" value="<?php echo $editar_dados['id']; ?>">
        <?php endif; ?>
        <input type="text" name="numero" placeholder="Número *" required style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo $editar_dados['numero'] ?? ''; ?>"><br><br>
        <input type="text" name="nome" placeholder="Nome *" required style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo $editar_dados['nome'] ?? ''; ?>"><br><br>
        <input type="email" name="email" placeholder="Email" style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo $editar_dados['email'] ?? ''; ?>"><br><br>

        <select name="horario" style="font-size:1.2em; padding:10px; width:320px;">
            <option value="">-- Selecionar Horário --</option>
            <?php foreach ($horarios as $h): ?>
                <option value="<?php echo $h['id']; ?>" <?php echo (isset($editar_dados['horario_id']) && $editar_dados['horario_id'] == $h['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($h['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Acesso a Relatórios:</label><br>
        <select name="relatorio" style="font-size:1.2em; padding:10px; width:320px;">
            <option value="utilizador" <?php echo (isset($editar_dados['relatorio_acesso']) && $editar_dados['relatorio_acesso'] === 'utilizador') ? 'selected' : ''; ?>>Utilizador</option>
            <option value="todos" <?php echo (isset($editar_dados['relatorio_acesso']) && $editar_dados['relatorio_acesso'] === 'todos') ? 'selected' : ''; ?>>Todos</option>
        </select><br><br>

        <input type="text" name="utilizador" placeholder="Utilizador *" required style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo $editar_dados['utilizador'] ?? ''; ?>"><br><br>
        <input type="password" name="password" placeholder="Password *" required style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo $editar_dados['password'] ?? ''; ?>"><br><br>

        <button type="submit" name="<?php echo $editar_dados ? 'editar' : 'criar'; ?>" class="botao">
            <?php echo $editar_dados ? 'Atualizar Funcionário' : 'Criar Funcionário'; ?>
        </button>
    </form>

    
    
    <h3>Funcionários Existentes</h3>

    <form method="get" style="margin-bottom: 20px;">
        <input type="text" name="pesquisa" placeholder="Procurar por número, nome ou email" style="width: 300px; padding: 10px; font-size: 1em;" value="<?php echo isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : ''; ?>">
        <button type="submit" class="botao" style="padding:6px 14px; font-size:0.9em;">Pesquisar</button>
    </form>

    <table border="0" cellpadding="10" cellspacing="0" style="width:100%%; background:#fff; border-collapse: collapse;">
        <thead style="background-color:#d4af37; color:black;">
            <tr>
                <th>Número</th>
                <th>Nome</th>
                <th>Email</th>
<th>Horário</th>
                <th>Utilizador</th>
                <th>Relatórios</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $termo = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
        $lista = array_filter($funcionarios, function($f) use ($termo) {
            if ($termo === '') return true;
            return stripos($f['numero'], $termo) !== false ||
                   stripos($f['nome'], $termo) !== false ||
                   stripos($f['email'], $termo) !== false;
        });
        ?>
        <?php if (empty($lista)): ?>
            <tr><td colspan="6" style="text-align:center;">Nenhum funcionário encontrado.</td></tr>
        <?php else: ?>
            <?php foreach ($lista as $f): ?>
                <tr style="border-bottom:1px solid #ccc;">
                    <td><?php echo htmlspecialchars($f['numero']); ?></td>
                    <td><?php echo htmlspecialchars($f['nome']); ?></td>
                    <td><?php echo htmlspecialchars($f['email']); ?></td>
<td><?php echo isset($f['horario_nome']) ? htmlspecialchars($f['horario_nome']) : ''; ?></td>
                    <td><?php echo htmlspecialchars($f['utilizador']); ?></td>
                    <td><?php echo htmlspecialchars($f['relatorio_acesso']); ?></td>
                    <td>
                        <a href="?editar=<?php echo $f['id']; ?>" class="botao" style="padding:6px 14px; font-size:0.9em;">Editar</a>
                        <a href="?eliminar=<?php echo $f['id']; ?>" class="botao" style="padding:6px 14px; font-size:0.9em;" style="background-color:#c00;" onclick="return confirm('Eliminar este funcionário?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
<br><a href="painel.php" class="botao">Voltar</a>
</div>
</body>
</html>
