<?php 
session_start(); //sessão padrão para manter infos
include "conecta.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';  //importa clases do PHPMailer

$mensagem = "";
$etapa = "email"; //logica para ir andando conforme etapas

function gerarTokenUnico($conect) { //garante a geração de um token aleatorio e q seja unico, não exita outro igual no banco
    do {        //tranforma 4 bytes, q são equivalente a 8 digitos em exadecimal
        $token = bin2hex(random_bytes(4)); //gera 4 byte randon
        $verifica = mysqli_query($conect, "SELECT id FROM recuperacao WHERE token = '$token'");
    } while(mysqli_num_rows($verifica) > 0);    //verfica se token ja não esta em uso, se não...
    return $token;                          //do ... while - para repetir processo até achar token q nn exista cadastrado ainda
}

// inicia quando login e email forem enviados
if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['email']) and isset($_POST['login'])) {
    $email = $_POST['email'];
    $login = $_POST['login'];

        // Verifica se o login e email estão relacionados em paciente
    $verifica_paciente = mysqli_query($conect, "SELECT * FROM usuarios WHERE email = '$email' AND login = '$login'");
        // Verifica se o login e email estão relacionados em empresa
    $verifica_empresa = mysqli_query($conect, "SELECT * FROM empresas WHERE email = '$email' AND login = '$login'");
        // Verifica se o login e email estão relacionados em administrador
    $verifica_adm = mysqli_query($conect, "SELECT * FROM adm WHERE email = '$email' AND login = '$login'");

    //se encontrado, continua o processo
    if (mysqli_num_rows($verifica_paciente) > 0 || mysqli_num_rows($verifica_empresa) > 0 || mysqli_num_rows($verifica_adm) > 0) {
        $token = gerarTokenUnico($conect);  //chama função e o token unico é gerado
        $agora = date('Y-m-d H:i:s');   //registro do horario de geração do token no banco

        //guardar o token no banco para poder dfazer a verifcação dele dops
        mysqli_query($conect, "INSERT INTO recuperacao (email, token, criado_em) VALUES ('$email', '$token', '$agora')");

        // Envia e-mail
        $mail = new PHPMailer(true);
        try { //abre a tentativa para enviar gmail (try um iff para excxções)
            $mail->isSMTP();    //mailer e configuração do email de app da email iff
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'arthur.2023316502@aluno.iffar.edu.br'; 
            $mail->Password   = 'aiwb zqky lxym zsls'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
                //configura a formatação do gmail q vai enviar
            $mail->setFrom('arthur.2023316502@aluno.iffar.edu.br', 'Bah, esqueceu a senha...');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = ' Seu token chegou !';
            $mail->Body    = "O código de recuperação é: <strong>$token</strong>";
                //envia o gmail. guarda na sessão, e paça para a próxima etapa, usando a lógica de iff conforme atualiza
            $mail->send();
            $_SESSION['recuperacao_email'] = $email;
            $etapa = "token";
        } catch (Exception $e) { //se der erro no envio entra...
            $mensagem = "<p class='erro'>Erro ao enviar e-mail: {$mail->ErrorInfo}</p>";
        }
    } else {
        $mensagem = "<p class='erro'>Login e e-mail não estão relacionados.</p>";
    }
}

// verifica na sessão se o token fori enviadom, passa erapa...
if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['token'])) {
    $email = $_SESSION['recuperacao_email'];    //pega na sessão emaiç
    $token_digitado = $_POST['token']; //vem form
        //bnusca para ver se existe token oara o email, e pega o mais recente
    $busca = mysqli_query($conect, "SELECT * FROM recuperacao WHERE email = '$email' AND token = '$token_digitado' ORDER BY criado_em DESC LIMIT 1");
    //se a busca tiver um result
    if (mysqli_num_rows($busca) > 0) {
        $dados = mysqli_fetch_assoc($busca);
        $criado_em = strtotime($dados['criado_em']);
        $agora = time();    //para ver a hora de agora

        if (($agora - $criado_em) <= 3600) { // hora atual, menos hora de envio, menos de 60 segundos...
            $etapa = "nova_senha"; //atualiza logica para passar próxima parte
        } else {
            $mensagem = "<p class='erro'>Token expirado. Tente novamente.</p>";
            $etapa = "email";
        }
    } else {
        $mensagem = "<p class='erro'>Token inválido.</p>";
        $etapa = "token";
    }           //atualiza logica para passar próxima parte
}

// verifica na sessão se existe o nova senha, ...
if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['nova_senha'])) {
    $email = $_SESSION['recuperacao_email'];
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];
            //infos do form;;;
    if ($nova_senha !== $confirma_senha) {  //se na confirmação de snha ambas não serem iguais...
        $mensagem = "<p class='erro'>As senhas não coincidem. Tente novamente.</p>";
        $etapa = "nova_senha";
    } else {    //se não... a,abs serão iguais...
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT); //pega a nova senha e ja criptografa
            //tenta atualizar em ambas as tabelas, porem. sp atualiza naq tive o gmail q corresponde
        mysqli_query($conect, "UPDATE usuarios SET senha = '$senha_hash' WHERE email = '$email'");
        mysqli_query($conect, "UPDATE empresas SET senha = '$senha_hash' WHERE email = '$email'");
        mysqli_query($conect, "UPDATE adm SET senha = '$senha_hash' WHERE email = '$email'");

        $mensagem = "<p class='success'>Senha redefinida com sucesso! </p>";
        unset($_SESSION['recuperacao_email']);  //atualiza asessão q vai teer o novo gmail
        $etapa = "final"; //logica q opassa prox partes
    }
}   //dps só os form q rola conforme iff de variavel de etapa atualizado...
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="estilos_cseses/recuperar_senha.css">
</head>
<body>
    <div class="container">
        <h2>Recuperar Senha</h2>

        <?php if ($etapa === "email"): ?>
            <form method="POST">
                <input type="text" name="login" placeholder="Digite seu login" required><br><br>
                <input type="email" name="email" placeholder="Digite seu e-mail" required><br><br>
                <button type="submit" class="btn">Enviar Token</button>
            </form>
        <?php elseif ($etapa === "token"): ?>
            <form method="POST">
                <input type="text" name="token" placeholder="Digite o token recebido" required><br><br>
                <button type="submit" class="btn">Verificar Token</button>
            </form>
        <?php elseif ($etapa === "nova_senha"): ?>
            <form method="POST" onsubmit="return verificarSenhasIguais();">
                <input type="password" id="nova_senha" name="nova_senha" placeholder="Nova senha" required><br><br>
                <input type="password" id="confirma_senha" name="confirma_senha" placeholder="Confirmar nova senha" required><br><br>
                <button type="submit" class="btn">Redefinir Senha</button>
            </form>
            <script>
                function verificarSenhasIguais() {
                    const senha = document.getElementById("nova_senha").value;
                    const confirma = document.getElementById("confirma_senha").value;
                    if (senha !== confirma) {
                        alert("As senhas não coincidem.");
                        return false;
                    }
                    return true;
                }
            </script>
        <?php endif; ?>

        <?php echo $mensagem; ?>
        <br><a href="index.php" class="btn-voltar">Voltar ao Início</a>
    </div>
</body>
</html>