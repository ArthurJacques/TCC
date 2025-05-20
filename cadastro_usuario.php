<?php
include "conecta.php";
$mensagem = "";

//para puxar os dados q ja tinha sido prenchido no formulario dps
$cpf = "";
$nome = "";
$data_nascimento = "";
$telefone = "";
$endereco = "";
$email = "";
$login = "";

//função para validar cpf real, baseado no cálculo de verificação
function cpf_valido($cpf) {
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
//verificação do cpf
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += $cpf[$i] * (($t + 1) - $i);
        }
        $digito = (10 * $soma) % 11;
        $digito = ($digito == 10) ? 0 : $digito;
        if ($cpf[$t] != $digito) return false;
    }
    return true;
}

//exata mesma lógica do cadastro empresa sóq com mais informações pelo cadastro do pacoente
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cpf = $_POST["cpf"];
    $nome = $_POST["nome"];
    $data_nascimento = $_POST["data_nascimento"];
    $telefone = $_POST["telefone"];
    $endereco = $_POST["endereco"];
    $email = $_POST["email"];
    $login = $_POST["login"];
    $senha_digitada = $_POST["senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (!cpf_valido($cpf)) { //se cpf for diferente de um cpf valido...
        $mensagem = "CPF inválido.";
    } else if ($senha_digitada !== $confirmar_senha) {
        $mensagem = "As senhas não coincidem.";
    } else {
        $senha_criptografada = password_hash($senha_digitada, PASSWORD_DEFAULT);

        $sql_verifica = "SELECT cpf FROM usuarios WHERE cpf = '$cpf' OR login = '$login'";
        $resultado = mysqli_query($conect, $sql_verifica);

        if (mysqli_num_rows($resultado) > 0) {  //se retornar algum resultado, ja tem cpf ou login ja cadastrado né
            $mensagem = "CPF ou login já cadastrado.";
        } else {
            $sql_inserir = "INSERT INTO usuarios 
                            (cpf, nome, data_nascimento, telefone, endereco, email, login, senha, ativo) 
                            VALUES 
                            ('$cpf', '$nome', '$data_nascimento', '$telefone', '$endereco', '$email', '$login', '$senha_criptografada', 'sim')";

            if (mysqli_query($conect, $sql_inserir)) {
                $mensagem = "Cadastro realizado com sucesso!";
                $cpf = "";
                $nome = "";
                $data_nascimento = "";
                $telefone = "";
                $endereco = "";
                $email = "";
                $login = "";
            } else {
                $mensagem = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuario</title>
    <link rel="stylesheet" href="estilos_cseses/cadastros.css">
</head>
<body>
<div class="container">
    <form method="POST">
        <h2>Cadastro de Usuario</h2>

        <input type="text" name="cpf" placeholder="CPF (somente números)" required value="<?= htmlspecialchars($cpf) ?>">
        <input type="text" name="nome" placeholder="Nome completo" required value="<?= htmlspecialchars($nome) //puxa tudo os valor quando tiver ?>">
        <label for="data_nascimento"></label>                           
        <input type="date" name="data_nascimento" required value="<?= htmlspecialchars($data_nascimento) ?>">
        <input type="text" name="telefone" placeholder="Telefone (opcional)" value="<?= htmlspecialchars($telefone) ?>">
        <input type="text" name="endereco" placeholder="Endereço (opcional)" value="<?= htmlspecialchars($endereco) ?>">
        <input type="email" name="email" placeholder="E-mail (opcional)" value="<?= htmlspecialchars($email) ?>">
        <input type="text" name="login" placeholder="Login" required value="<?= htmlspecialchars($login) ?>">
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
        
        <button type="submit">Cadastrar</button>
        <?php if ($mensagem != ""): ?>
            <p class="mensagem"><?= $mensagem ?></p>
        <?php endif; ?>
    </form>
    <div class="link-login">
        <p>Já tem uma conta? <a href="login_usuario.php">Clique aqui para fazer login</a></p>
    </div>
</div>
</body>
</html>