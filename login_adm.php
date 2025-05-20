<?php
session_start();
include "conecta.php";
$mensagem_erro = "";
$login = "";
//mesmalógica queo  loginempresa...
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    $sql = "SELECT id, senha FROM adm WHERE login = '$login'";
    $resultado = mysqli_query($conect, $sql);

    if (mysqli_num_rows($resultado) > 0) {
        $dados = mysqli_fetch_assoc($resultado);
        if (password_verify($senha, $dados['senha'])) {
            $_SESSION['adm_id'] = $dados['id'];
            header("Location: painel_adm.php");
            exit();
        } else {
            $mensagem_erro = '<div class="erro-senha">
                                <p>Senha incorreta.</p>
                                <form action="recuperar_senha.php" method="POST">
                                    <input type="hidden" name="tipo" value="adm">
                                    <button type="submit" class="btn-recuperar">Esqueci minha senha</button>
                                </form>
                              </div>';
        }
    } else {
        $mensagem_erro = '<div class="erro-senha">
                            <p>Login não encontrado.</p>
                          </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login do Administrador</title>
    <link rel="stylesheet" href="estilos_cseses/login_adm.css">
</head>
<body>
    <div class="container">
        <h2>Login do Administrador</h2>
        <form method="POST">
            <input type="text" name="login" placeholder="Login" value="<?php echo htmlspecialchars($login); ?>" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit">Entrar</button>
        </form>

        <?php if (!empty($mensagem_erro)) { echo $mensagem_erro; } ?>

        <a href="index.php" class="btn-voltar">Voltar ao Início</a>
    </div>
</body>
</html>