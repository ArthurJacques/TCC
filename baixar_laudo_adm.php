<?php
session_start();
include "conecta.php";

//verifica se está logado como administrador
if (!isset($_SESSION['adm_id']) || empty($_SESSION['adm_id'])) {
    header("Location: index.php"); //se tentar acessar diretamente sem login, volta pro início
    exit();
}
$id = $_GET["id"];

$sql = "SELECT nome_arquivo, arquivo FROM laudos 
        WHERE id = '$id'";
        $dados = mysqli_query($conect, $sql);
$linha = mysqli_fetch_assoc($dados);
$nome = $linha["nome_arquivo"];
$arquivo = $linha["arquivo"];

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename=\"$nome\"");
header("Content-Length: " . strlen($arquivo));
echo $arquivo;
exit();
?>