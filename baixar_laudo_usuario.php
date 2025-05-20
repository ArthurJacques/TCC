<?php
//mesmo logica dp baixar laudo de empresa..

session_start();
include "conecta.php";

$id = $_GET["id"]; //sem verificação pq usuarios pode todos os laudos
$sql = "SELECT nome_arquivo, arquivo FROM laudos 
        WHERE id = '$id' ";
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