<?php 
session_start();
include "conecta.php";
//exatamente mesmo lógica de perfil da empresa, unica diferença utiçlozação disso...
$cpf_antigo = $_SESSION['cpf']; 

$sql = "SELECT nome, login, telefone, endereco, email FROM usuarios WHERE cpf = '$cpf_antigo' AND ativo = 'sim'";
$resultado = mysqli_query($conect, $sql);

if ($dados = mysqli_fetch_assoc($resultado)) {
    $nome = $dados['nome'];
    $login = $dados['login'];
    $telefone = $dados['telefone'];
    $endereco = $dados['endereco'];
    $email = $dados['email'];
} else {
    header("Location: painel_usuario.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['excluir'])) {
        $sql_delete = "UPDATE usuarios SET ativo = 'nao' WHERE cpf = '$cpf_antigo'";
        mysqli_query($conect, $sql_delete);
        session_destroy();
        header("Location: index.php");
        exit();
    }

    $novo_nome = $_POST['nome'];
    $novo_cpf = $_POST['cpf'];
    $novo_telefone = $_POST['telefone'];
    $novo_endereco = $_POST['endereco'];
    $novo_email = $_POST['email'];
    $novo_login = $_POST['login'];
    $nova_senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    if (!empty($nova_senha)) {
        if ($nova_senha === $confirma_senha) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql_update = "UPDATE usuarios SET nome = '$novo_nome', cpf = '$novo_cpf', telefone = '$novo_telefone', endereco = '$novo_endereco', email = '$novo_email', login = '$novo_login', senha = '$senha_hash' WHERE cpf = '$cpf_antigo'";
        } else {
            echo "<script>alert('A senha e a confirmação de senha não coincidem.'); window.history.back();</script>";
            exit();
        }
    } else {
        $sql_update = "UPDATE usuarios SET nome = '$novo_nome', cpf = '$novo_cpf', telefone = '$novo_telefone', endereco = '$novo_endereco', email = '$novo_email', login = '$novo_login' WHERE cpf = '$cpf_antigo'";
    }
    mysqli_query($conect, $sql_update);
//se teve alteração no cpf do paciente, muda não só no seu cadastro mas tbm em todos os laudos q estava para o seu cpf anterios antes da mudança
    if ($novo_cpf !== $cpf_antigo) {
        $sql_update_laudos = "UPDATE laudos SET cpf = '$novo_cpf' WHERE cpf = '$cpf_antigo'";
        mysqli_query($conect, $sql_update_laudos);
        $_SESSION['cpf'] = $novo_cpf;
    }

    header("Location: painel_usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="estilos_cseses/perfils.css">
    <script>
    function confirmarAlteracoes() {
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
    <h2>Meu Perfil</h2>
    <form method="POST" onsubmit="return confirmarAlteracoes();">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>" required>

        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" value="<?php echo $cpf_antigo; ?>" required>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo $telefone; ?>">

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco" value="<?php echo $endereco; ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $email; ?>">

        <label for="login">Login:</label>
        <input type="text" id="login" name="login" value="<?php echo $login; ?>" required>

        <label for="senha">Nova Senha:</label>
        <input type="password" id="senha" name="senha" placeholder="Deixe em branco para manter a senha atual">

        <label for="confirma_senha">Confirmar Nova Senha:</label>
        <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Confirme a nova senha(se houver)">

        <button type="submit">Salvar Alterações</button>
    </form>

    <form method="POST" onsubmit="return confirmarExclusao();">
        <input type="hidden" name="excluir" value="1">
        <br>
        <button type="submit" class="btn-excluir">Excluir Conta</button>
    </form>

    <a href="painel_usuario.php" class="btn-voltar">Voltar ao Painel</a>
</div>
</body>
</html>