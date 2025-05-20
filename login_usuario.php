<?php
//ezxatamente seguindo mesma logica q login ra empresa
session_start();
include "conecta.php";
$mensagem_erro = "";
$login = ""; //guardar o login pra continuar no campo se errar

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST["login"];
    $senha = $_POST["senha"];

    $sql = "SELECT cpf, senha FROM usuarios WHERE login = '$login' AND ativo = 'sim'";
    $resultado = mysqli_query($conect, $sql);

    if (mysqli_num_rows($resultado) > 0) {
        $linha = mysqli_fetch_assoc($resultado);
        if (password_verify($senha, $linha["senha"])) {
            $_SESSION["cpf"] = $linha["cpf"];
            header("Location: painel_usuario.php");
            exit();
        } else {
            $mensagem_erro = '<div class="erro-senha">
                                <p>Senha incorreta.</p>
                                <form action="recuperar_senha.php" method="POST">
                                    <input type="hidden" name="tipo" value="empresa_paciente">
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
    <title>Login do Usuario</title>
    <link rel="stylesheet" href="estilos_cseses/login_usuario.css">
</head>
<body>
<div class="container">
    
    <h2>Login do Usuario</h2>
    <form method="POST">
        <input type="text" name="login" placeholder="Login" value="<?php echo htmlspecialchars($login) //manter login; ?>" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>

    <?php if (!empty($mensagem_erro)) { echo $mensagem_erro; } ?>
    <a href="cadastro_usuario.php">Não tem cadastro? Cadastre-se agora!</a>
    <a href="index.php" class="btn-voltar">Voltar ao Início</a>
</div>
</body>
</html>