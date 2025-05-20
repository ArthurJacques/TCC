<?php
session_start(); //sessão para gurardar informação do banco entre diferentes paginas...
include "conecta.php";
//valors q são pegos da url pela tal da sesão
$id = $_GET["id"]; //para saber qual laudo
$empresa_id = $_SESSION["empresa_id"]; //só pode lauda da emresa

$sql = "SELECT nome_arquivo, arquivo FROM laudos 
        WHERE id = '$id' AND id_empresa = '$empresa_id'";
            $dados = mysqli_query($conect, $sql); //consulta pro banquinhp
$linha = mysqli_fetch_assoc($dados); //faz array primeira linha do resultados, pra separar variaveis
$nome = $linha["nome_arquivo"];
$arquivo = $linha["arquivo"];

header("Content-Type: application/pdf"); //sera enviado um pdf
header("Content-Disposition: inline; filename=\"$nome\"");  //pra ver o pdf em pagina sem ter q baixa
header("Content-Length: " . strlen($arquivo)); //pra tamnho conteudo
echo $arquivo; //imprime o binario convertido
exit(); //finalizado não deixa nada mais acontecer depois(necessario ja ocorrido né)
?>