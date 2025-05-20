<?php
session_start(); //manter infos empresa logada
    //inclui o PHPMailer, mesmo padrao da recup de senha implemetnado para avisar q um novo laudo foi cadastrado para tal pessoa, quando pload do laudo da okk
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require 'vendor/autoload.php';  //importa clases do PHPMailer

// isset verifica se variavel existe... ! = nega. Ent se empresa id nn existe na sessão  sessão não existe
if (!isset($_SESSION['empresa_id']) || empty($_SESSION['empresa_id'])) {
    header("Location: index.php");      //ou exite e esta vazia
    exit();     //vai ler o index, ou seja, redirecionar
}   //ja encerra php para nem chegar a carrear nada

include "conecta.php";

$id_empresa = $_SESSION['empresa_id']; //pega id pra empresa so ver seus laudos
$cpf_selecionado = $_GET["cpf"] ?? "";
$mensagem = "";         //mesmo esquema do excluir laudo, pega cpf passado url se não string vazia, só por segurança..

if ($_SERVER["REQUEST_METHOD"] === "POST") { //pra executa so se o form for enviado
    $cpf = $_POST["cpf"];

    //verifica se o CPF existe no sistema
    $verifica = mysqli_query($conect, "SELECT email FROM usuarios WHERE cpf = '$cpf' LIMIT 1");
    if (mysqli_num_rows($verifica) === 0) {
        $mensagem = "Erro ao salvar o laudo: CPF informado não está cadastrado.";
    } else {
        $nome_arquivo = $_FILES["arquivo"]["name"]; //nome arquivo
        $arquivo_tmp = $_FILES["arquivo"]["tmp_name"]; //caminho temporari do arquivo
        $arquivo_dados = file_get_contents($arquivo_tmp); //le o conterudo do arquivo concvertendo para dados binarios para ser guardado no banco .blob
        $sql = "INSERT INTO laudos (cpf, id_empresa, nome_arquivo, arquivo)
                VALUES ('$cpf', '$id_empresa', '$nome_arquivo', ?)"; //comando para inserir um novo laudo
                                                            //arquivo dados binarios separado para ser tratado, por isso ?, q vai pegar q vem dps
            $stmt = mysqli_prepare($conect, $sql); //prepara contra injeciton mysql
            mysqli_stmt_bind_param($stmt, "s", $arquivo_dados); //diz parameto ? pra enviar sql é string, conteudo prox - assim envia sem possivel perda

        //guarda nas mensagens oq aconteceu para dar retorno pro usario
        if (mysqli_stmt_execute($stmt)) {
            $mensagem = "Laudo enviado com sucesso!";

    // se o laudo foi enviado ok... ce se o cpf tem algum login com ele na sql
    $sql_email = "SELECT email FROM usuarios WHERE cpf = '$cpf' LIMIT 1"; //busca o email do paciente q tem cpf do laudo
    $res_email = mysqli_query($conect, $sql_email);
    $linha_email = mysqli_fetch_assoc($res_email);
    $email_destino = $linha_email['email']; //pega o email da pessoa com aquele cpf

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'arthur.2023316502@aluno.iffar.edu.br';
        $mail->Password = 'aiwb zqky lxym zsls';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('arthur.2023316502@aluno.iffar.edu.br', 'Laudos.aqui');
        $mail->addAddress($email_destino);

        $mail->isHTML(true);
        $mail->Subject = 'Um novo laudo seu !';
        $mail->Body    = "Olá! Um novo laudo foi cadastrado para o CPF <strong>$cpf</strong> em sua conta. <br>Acesse sua área de login para visualizar.";

        $mail->send(); //envia o email
    } catch (Exception $e) {
        //falha ao enviar e-mail, mas como o laudo foi salvo com sucesso, tanto faz, só nn vai enviar um gmail no caso do proprio laudo pra quem for cadatrado nem tiver login para receber notificação
        //echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
    }
        } else {
            $mensagem = "Erro ao salvar o laudo: ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel da Empresa</title>
    <link rel="stylesheet" href="estilos_cseses/painel_empresa.css">
    <script>
        function confirmarExclusao(id) { //java de pop up para confirmar exclusção
            if (confirm("Tem certeza que deseja excluir este laudo? Essa ação não poderá ser desfeita.")) {
                window.location.href = "excluir_laudo.php?id=" + id; //deixa id ir para o php q faz a exclusao
            }
        }
    </script>
</head>
<body>
    <div class="container">
    <div class="botoes-topo">
        <a href="perfil_empresa.php" class="btn-perfil">Perfil da Empresa</a>
    </div>

        <?php
        if ($mensagem != "") {  //se tiver mensagem, mostra na tela com a classe para te estilização
            $classe = str_contains($mensagem, "sucesso") ? "success" : "erro";
            echo "<p class='$classe'>$mensagem</p>";
        }
        ?>

        <h2>Enviar novo laudo</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="cpf" placeholder="CPF do paciente" required><br>
            <input type="file" name="arquivo" accept="application/pdf" required><br>
            <button type="submit" class="btn">Enviar Laudo</button>
        </form>

        <h2>Pacientes com laudos</h2>
        <table>
            <tr><th>CPF</th><th>Ações</th></tr>
            <?php //busca de todos os cpf q tem laudos ativo com a emoresa logada
            $sql = "SELECT DISTINCT cpf FROM laudos WHERE id_empresa = '$id_empresa' AND ativo = 'sim'";
            $dados = mysqli_query($conect, $sql);
                //pedido pro banco dos valores e imprimir em tabela com a repetição
            while ($linha = mysqli_fetch_assoc($dados)) {
                echo "<tr>";
                echo "<td>" . $linha["cpf"] . "</td>";
                echo "<td><a href='?cpf=" . $linha["cpf"] . "' class='btn'>Ver Laudos</a></td>";
                echo "</tr>";
            }
            ?>
        </table>

        <?php
if ($cpf_selecionado != "") { //se tiver um cpf selecionado, aparece na url, por causa do server q relaciona essa variavel,e ai mostra todos os laudos do cpf relacionados a empresa q ta logada
    echo "<h2>Laudos do CPF: $cpf_selecionado</h2>";
    echo "<table>";
    echo "<tr><th>Nome do Arquivo</th><th>Data</th><th>Download</th><th>Excluir</th></tr>"; 
//criação do cabeçario tabela acima,e abaico bucsva bancoi laudos do cpf refernte spi aquela empresa
        $sql = "SELECT id, nome_arquivo, data_envio FROM laudos 
        WHERE cpf = '$cpf_selecionado' AND id_empresa = '$id_empresa' AND ativo = 'sim'";
    $laudos = mysqli_query($conect, $sql);

    if (mysqli_num_rows($laudos) > 0) {
        while ($linha = mysqli_fetch_assoc($laudos)) { // Repetição imprimindo tabela com os laudos e informações
            echo "<tr>";
            echo "<td>" . $linha["nome_arquivo"] . "</td>";
                $data_envio = $linha["data_envio"]; // Formatação data 
                $data_formatada = date("d/m/Y H:i:s", strtotime($data_envio));
                echo "<td>" . $data_formatada . "</td>";
            echo "<td><a class='btn' href='baixar_laudo_empresa.php?id=" . $linha["id"] . "' target='_blank'>Visualizar</a></td>"; // Botão para visualizar o laudo
            echo "<td><button class='btn' onclick='confirmarExclusao(" . $linha["id"] . ")'>Excluir</button></td>"; // Botão para excluir o laudo
            echo "</tr>";
        } 
    } else { //se não sem laudos,,, aviso
        echo "<tr><td colspan='4'>Nenhum laudo encontrado.</td></tr>";
    }
    echo "</table>";
}  
?>
        <a href="logaut.php" class="btn-voltar">Voltar ao Início</a>
    </div>
</body>
</html>