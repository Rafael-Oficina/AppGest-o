
<?php
session_start();
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilizador = $_POST["utilizador"];
    $password = $_POST["password"];

    $ficheiro_password = "../admin_password.txt";

    if (file_exists($ficheiro_password)) {
        $password_correta = trim(file_get_contents($ficheiro_password));
    } else {
        $password_correta = "123456";
    }

    if ($utilizador === "Administrador" && $password === $password_correta) {
        $_SESSION["admin_logado"] = true;
        header("Location: painel.php");
        exit();
    } else {
        $erro = "Utilizador ou password incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login Administração</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container">
        <h1>Administração</h1>
        <form method="post">
            <input type="text" name="utilizador" placeholder="Utilizador" required style="font-size: 1.2em; padding: 10px; width: 250px;"><br><br>
            <input type="password" name="password" placeholder="Password" required style="font-size: 1.2em; padding: 10px; width: 250px;"><br><br>
            <button type="submit" class="botao">Entrar</button>
        </form>
        <?php if ($erro): ?>
            <p style="color: red;"><?php echo $erro; ?></p>
        <?php endif; ?>
        <br>
        <a href="../index.php" class="botao">Voltar</a>
    </div>
</body>
</html>
