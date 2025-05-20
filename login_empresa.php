<?php
session_start(); //guardar as info e pegar de empresa ka logada url e tal
include "conecta.php";
$erro = ""; //para giardar mensagens
$mensagem_erro = "";
$login = ""; //armazenar o login preenchido para manter se errar

if ($_SERVER["REQUEST_METHOD"] === "POST") { //ver os dados só depois do clique em entrar pra nn te erro monte coisa não definidade q vai ser usada
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    $sql = "SELECT id, senha FROM empresas WHERE login = '$login' AND ativo = 'sim'"; //pega empresa cadastrada q tem o mesmo login, portanto q esteja ativa..
    $resultado = mysqli_query($conect, $sql); //verifica o login e senha botado

    if (mysqli_num_rows($resultado) > 0) { //se encontrado algum resultado no banco... ent login exite
        $dados = mysqli_fetch_assoc($resultado); // ja coloca em array
        if (password_verify($senha, $dados['senha'])) { // passward_verify discptografa q foi feito com função oposto dele e verifica se ta igual oq usuario digito
            $_SESSION['empresa_id'] = $dados['id']; //guarda na sessão 
            header("Location: painel_empresa.php");
            exit();     //redirecionamento para o painel da empresa e finalizando pra nada mais ocorrer
        } else {
            //senha incorreta, mas login existe
            $mensagem_erro = '<div class="erro-senha">
                                <p>Senha incorreta.</p>
                                <form action="recuperar_senha.php" method="POST">
                                    <input type="hidden" name="tipo" value="empresa_paciente">
                                    <button type="submit" class="btn-recuperar">Esqueci minha senha</button>
                                </form>
                              </div>';
        }
    } else {
        //login não encontrado no banco
        $mensagem_erro = '<div class="erro-senha">
                            <p>Login não encontrado.</p>
                          </div>';
    }
} //abaixo, html formizinho de tudo q tinha sido pego de info solicitada
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login da Empresa</title>
    <link rel="stylesheet" href="estilos_cseses/login_empresa.css">
</head>
<body>
    <div class="container">
        <h2>Login da Empresa</h2>
        <form method="POST">
            <input type="text" name="login" placeholder="Login" value="<?php echo htmlspecialchars($login); //manter login se errar ?>" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit">Entrar</button>
        </form>

        <?php if (!empty($mensagem_erro)) { echo $mensagem_erro; } //se existe mesagem de erro, imprime ela, q ja ta com o html junto na variavel ?>

        <a href="cadastro_empresa.php">Não tem cadastro? Cadastre-se agora!</a>
        <a href="index.php" class="btn-voltar">Voltar ao Início</a>
    </div>
</body>
</html>