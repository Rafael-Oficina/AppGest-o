<?php
session_start();
if (!isset($_SESSION["admin_logado"])) {
    header("Location: login.php");
    exit();
}

require_once "config_bd.php";

$mensagem = "";
$erro = "";

// Criar novo motivo
// Buscar motivo para edição
$motivo_editar = null;
if (isset($_GET["editar"])) {
    $id = (int) $_GET["editar"];
    try {
        $stmt = $ligacao->prepare("SELECT * FROM motivos_pausa WHERE id = ?");
        $stmt->execute([$id]);
        $motivo_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erro = "Erro ao buscar motivo para edição: " . $e->getMessage();
    }
}

// Atualizar motivo
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizar"])) {
    $id = (int) $_POST["editar_id"];
    $codigo = trim($_POST["codigo"]);
    $descricao = trim($_POST["descricao"]);

    if ($codigo && $descricao) {
        try {
            $stmt = $ligacao->prepare("UPDATE motivos_pausa SET codigo = ?, descricao = ? WHERE id = ?");
            $stmt->execute([$codigo, $descricao, $id]);
            $mensagem = "Motivo atualizado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao atualizar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios.";
    }
}

// Eliminar
if (isset($_GET["eliminar"])) {
    $id = (int) $_GET["eliminar"];
    try {
        $stmt = $ligacao->prepare("DELETE FROM motivos_pausa WHERE id = ?");
        $stmt->execute([$id]);
        $mensagem = "Motivo eliminado com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao eliminar: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["criar"])) {
    $codigo = trim($_POST["codigo"]);
    $descricao = trim($_POST["descricao"]);

    if ($codigo && $descricao) {
        try {
            $stmt = $ligacao->prepare("INSERT INTO motivos_pausa (codigo, descricao) VALUES (?, ?)");
            $stmt->execute([$codigo, $descricao]);
            $mensagem = "Motivo criado com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao criar: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios.";
    }
}

// Buscar motivos existentes
$motivos = [];
try {
    $stmt = $ligacao->query("SELECT * FROM motivos_pausa ORDER BY codigo ASC");
    $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao buscar motivos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Motivos de Pausa</title>
    <link rel="stylesheet" href="../style.css">
    
    

</head>
<body>
    <div class="container">
        <h1>Gestão de Motivos de Pausa</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem" style="color:green;"><?php echo $mensagem; ?></p>
        <?php endif; ?>
        <?php if (!empty($erro)): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group"><input type="text" id="codigo" name="codigo" required class="campo" placeholder="Código" style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo isset($motivo_editar) ? htmlspecialchars($motivo_editar['codigo']) : ''; ?>">
            </div>

            <div class="form-group"><input type="text" id="descricao" name="descricao" required class="campo" placeholder="Descrição" style="font-size:1.2em; padding:10px; width:300px;" value="<?php echo isset($motivo_editar) ? htmlspecialchars($motivo_editar['descricao']) : ''; ?>">
            </div>

            <?php if ($motivo_editar): ?>
    <input type="hidden" name="editar_id" value="<?php echo $motivo_editar['id']; ?>">
    <button type="submit" name="editar" class="botao" style="background-color:#d4af37; color:black; font-weight:500; border:none; border-radius:10px; padding:15px 40px; width:250px; font-size:1.1em; box-shadow:0px 2px 5px rgba(0,0,0,0.2);">Atualizar Motivo</button>
<?php else: ?>
    <div style="text-align:center;"><button type="submit" name="criar" class="botao" style="background-color:#d4af37; color:black; font-weight:500; border:none; border-radius:10px; padding:15px 40px; width:250px; font-size:1.1em; box-shadow:0px 2px 5px rgba(0,0,0,0.2);">Criar Motivo</button></div>
<?php endif; ?>
        </form>

        <h2>Motivos Existentes</h2>
        <table border="0" cellpadding="10" cellspacing="0" style="margin: 0 auto; background:#fff; border-collapse: collapse;" class="tabela">
            <thead style="background-color:#d4af37; color:black;">
<tr>
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($motivos)): ?>
                    <tr style="border-bottom:1px solid #ccc;"><td colspan="2">Nenhum motivo registado.</td></tr>
                <?php else: ?>
                    <?php foreach ($motivos as $motivo): ?>
                        
<tr style="border-bottom:1px solid #ccc;">
    <td><?php echo htmlspecialchars($motivo['codigo']); ?></td>
    <td><?php echo htmlspecialchars($motivo['descricao']); ?></td>
    
<td style="display:flex; gap:10px;">
    <a href="?editar=<?php echo $motivo['id']; ?>" class="botao" style="padding:6px 14px; font-size:0.9em;">Editar</a>
    <a href="?eliminar=<?php echo $motivo['id']; ?>" class="botao" style="padding:6px 14px; font-size:0.9em;" style="background-color:#c00;" onclick="return confirm('Eliminar este motivo?');">Eliminar</a>
</td>

</tr>

                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="painel.php" class="botao">Voltar</a>
    </div>
</body>
</html>
