<?php
session_start(); //manter infos enquant o logado

// se o usuário tentar acessar sem login, redireciona
if (!isset($_SESSION['cpf']) || empty($_SESSION['cpf'])) {
    header("Location: index.php");
    exit();
}

include "conecta.php";
$cpf = $_SESSION['cpf']; //pega cpf do logado q veio do login 

$sql = "SELECT nome FROM usuarios WHERE cpf = '$cpf'";
$paciente = mysqli_query($conect, $sql); //busca o nome do paciente q tiver um cpf igual doq foi logado
$nome = ""; //guarda só para mostrar o bem vindo dps
if ($dados = mysqli_fetch_assoc($paciente)) {
    $nome = $dados["nome"]; //poe nome em um array
}

// busca de todos os laudos relacionados ao cpf, incluindo login da empresa
$sql = "SELECT l.id, l.nome_arquivo, l.data_envio, e.login AS login_empresa 
        FROM laudos l 
        LEFT JOIN empresas e ON l.id_empresa = e.id 
        WHERE l.cpf = '$cpf' AND l.ativo = 'sim'";
$laudos = mysqli_query($conect, $sql); //variavel guardando todos os laudos do paciente
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Usuario</title>
    <link rel="stylesheet" href="estilos_cseses/painel_usuario.css">
</head>
<body>
<div class="container">
    <h1>Bem-vindo, <?php echo $nome; ?></h1>

    <div class="botoes-topo">
        <a href="perfil_usuario.php" class="btn-perfil">Meu Perfil</a>
    </div>
    <h2>Seus Laudos</h2>
    <table>
        <tr><th>Nome do Arquivo</th><th>Data de Upload</th><th>Empresa Responsável</th><th>Visualizar</th></tr>
        <?php 
        if (mysqli_num_rows($laudos) > 0) { // se exite laudos para o cpf do paciente...
            while ($d = mysqli_fetch_assoc($laudos)) { //imprime em tabela pela repetição
                echo "<tr>";
                echo "<td>" . $d["nome_arquivo"] . "</td>";
                    $data_formatada = date("d/m/Y", strtotime($d["data_envio"])); // formata data no padrão brasileiro
                    echo "<td>" . $data_formatada . "</td>";
                echo "<td>" . ($d["login_empresa"] ?? "Desconhecida") . "</td>"; // se empresa não encontrada, mostra desconhecida
                echo "<td><a class='download-btn' href='baixar_laudo_usuario.php?id=" . $d["id"] . "' target='_blank'>Visualizar</a></td>";
                echo "</tr>";
            }
        } else { //c não tabela com nada
            echo "<tr><td colspan='4'>Nenhum laudo encontrado para seu CPF.</td></tr>";
        }
        ?>
    </table>

    <a href="logaut.php" class="btn-voltar">Voltar ao Início</a>
</div>
</body>
</html>