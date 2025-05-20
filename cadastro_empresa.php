<?php
include "conecta.php";
//para qualquer mensagem q aparecer ser guardada para ser dizida depois
$mensagem = ""; 
$login = "";
$email = "";
$nome = "";
$telefone = "";
$endereco = "";
// vazio para por valores iniciais caso erre retorne
if ($_SERVER["REQUEST_METHOD"] === "POST") { //ve se o form foi enviado e esta carregando pagina
    $login = $_POST["login"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];
    $nome = $_POST["nome"];
    $telefone = $_POST["telefone"];
    $endereco = $_POST["endereco"];

    if ($senha !== $confirmar_senha) { //ver se as senhas são igual
        $mensagem = "As senhas não coincidem.";
    } else { //se senha igual...
        $senha_criptografada = password_hash($senha, PASSWORD_DEFAULT); //criptografa o texto pra nn ficar vazado no banco

        $sql = "SELECT id FROM empresas WHERE login = '$login'"; //ve se nn tem ninguem memso login
        $resultado = mysqli_query($conect, $sql); //pega as info de geral pra ver os login de outras

        if (mysqli_num_rows($resultado) > 0) { //roda a repetição verifiando pra ver se tem login igual
            $mensagem = "Login já está em uso. Escolha outro.";
        } else { //se não... tudo certo para largar o cadastro pro banco
            $sql = "INSERT INTO empresas (nome, telefone, endereco, login, email, senha, ativo) 
                    VALUES ('$nome', '$telefone', '$endereco', '$login', '$email', '$senha_criptografada', 'sim')";
            //verifica se funfou ou não o cadastro, e avisa...
            if (mysqli_query($conect, $sql)) {
                $mensagem = "Cadastro realizado com sucesso!";
                $login = "";
                $email = "";
                $nome = "";
                $telefone = "";
                $endereco = "";
            } else {
                $mensagem = "Erro ao cadastrar: " . mysqli_error($conect);
            }
        }
    }
}
//embaixo, todo o form das informações q foram pega pra lançar no php
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Empresa</title>
    <link rel="stylesheet" href="estilos_cseses/cadastros.css">
</head>
<body>
<div class="container">
    <form method="POST">
        <h2>Cadastro de Empresa</h2>

        <input type="text" name="nome" placeholder="Nome da Empresa" required value="<?= htmlspecialchars($nome) ?>">
        <input type="text" name="telefone" placeholder="Telefone" value="<?= htmlspecialchars($telefone) ?>">
        <input type="text" name="endereco" placeholder="Endereço" value="<?= htmlspecialchars($endereco) ?>">
        <input type="text" name="login" placeholder="Login" required value="<?= htmlspecialchars($login) //depois q tiver, retorna oq ja havia sido colocado como cadastro ?>">
        <input type="email" name="email" placeholder="Email (Gmail)" required value="<?= htmlspecialchars($email) ?>">
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>

        <button type="submit">Cadastrar</button>
        <?php if ($mensagem != ""): //exibe mangem na tela dos if do php doq aconteceu...?>
            <p class="mensagem"><?= $mensagem ?></p>
        <?php endif; ?>
    </form>
    <div class="link-login">
        <p>Já tem uma conta? <a href="login_empresa.php">Clique aqui para fazer login</a></p>
    </div>
</div>
</body>
</html>