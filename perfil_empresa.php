<?php
session_start(); //pra lembrar oq precisa deq esta logado
include "conecta.php";
$id_empresa = $_SESSION['empresa_id']; //pega id doq logado

$sql = "SELECT login, email, nome, telefone, endereco FROM empresas WHERE id = '$id_empresa' AND ativo = 'sim'"; 
$resultado = mysqli_query($conect, $sql); //bica o login e e gmail doq ta logado, e gurada variavel

if ($dados = mysqli_fetch_assoc($resultado)) { //se encontrou emoresa relacionada... giarda seus dados
    $login = $dados['login'];
    $gmail = $dados['email'];
    $nome = $dados['nome'];
    $telefone = $dados['telefone'];
    $endereco = $dados['endereco'];
} else { //se não envontrou ou acoteceu erro, ja manda de volta painael
    header("Location: painel_empresa.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") { //quando foor enviado...
    if (isset($_POST['excluir'])) {
        //se clicou em excluir, apaga a empresa e volta pro index
        $sql_delete = "UPDATE empresas SET ativo = 'nao' WHERE id = '$id_empresa'";
        mysqli_query($conect, $sql_delete);
        session_destroy();
        header("Location: index.php");
        exit();
    }

    $novo_login = $_POST['login'];
    $novo_gmail = $_POST['gmail'];
    $nova_senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    $novo_nome = $_POST['nome'];
    $novo_telefone = $_POST['telefone'];
    $novo_endereco = $_POST['endereco'];
//recebe os dados enviados
    if (!empty($nova_senha)) {//ve se compo senha foi prenchido, pq se nn foi, nn tem senha akterar
        if ($nova_senha === $confirma_senha) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT); //pq senha tem cuidado de criptografar, tem q ser separado
            $sql_update = "UPDATE empresas SET login = '$novo_login', email = '$novo_gmail', senha = '$senha_hash', nome = '$novo_nome', telefone = '$novo_telefone', endereco = '$novo_endereco' WHERE id = '$id_empresa'";
        } else {
            echo "<script>alert('A senha e a confirmação de senha não coincidem.'); window.history.back();</script>";
            exit();
        }
    } else { //se não só altera o resto, sem a senha...
        $sql_update = "UPDATE empresas SET login = '$novo_login', email = '$novo_gmail', nome = '$novo_nome', telefone = '$novo_telefone', endereco = '$novo_endereco' WHERE id = '$id_empresa'";
    }
    mysqli_query($conect, $sql_update);
    //depois de executar as alterações, manda d volta pro painel da empresa, e finaliza pra nn ter qualqeur operação após
    header("Location: painel_empresa.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil da Empresa</title>
    <link rel="stylesheet" href="estilos_cseses/perfils.css">
    <script> //pede confirmação usuario antes de enviar o formulario pop uo funcção kajva
    function confirmarAlteracao() { 
        var senha = document.getElementById("senha").value;
        var confirma = document.getElementById("confirma_senha").value;
        if (senha !== "" && senha !== confirma) {
            alert("A senha e a confirmação de senha não coincidem.");
            return false;
        }
        return confirm("Tem certeza que deseja salvar as alterações?");
    }
    function confirmarExclusao() {
        return confirm("Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.");
    }
    </script>
</head>
<body>
<div class="container">
    <h2>Perfil da Empresa</h2>
    <form method="POST" onsubmit="return confirmarAlteracao();"> <!-- só submete se tiver o retorno ok do pop up função q foi criada-->
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo $telefone; ?>" required>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" value="<?php echo $endereco; ?>" required>

        <label for="login">Login:</label>
        <input type="text" id="login" name="login" value="<?php echo $login; ?>" required>

        <label for="gmail">Gmail:</label>
        <input type="email" id="gmail" name="gmail" value="<?php echo $gmail; ?>" required>

        <label for="senha">Nova Senhas:</label>
        <input type="password" id="senha" name="senha" placeholder="Deixe em branco para manter a senha atual">

        <label for="confirma_senha">Confirmar Nova Senha:</label>
        <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Confirme a nova senha(se houver)">

        <button type="submit">Salvar Alterações</button>
    </form>
    <br>
    <form method="POST" onsubmit="return confirmarExclusao();">
        <input type="hidden" name="excluir" value="1">
        <button type="submit" class="btn-excluir">Excluir Conta</button>
    </form>
    <a href="painel_empresa.php" class="btn-voltar">Voltar ao Painel</a>
</div>
</body>
</html>