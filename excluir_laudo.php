<?php
session_start(); //sessão para gurardar informação do banco entre diferentes paginas...
include "conecta.php";

$id_empresa = $_SESSION["empresa_id"]; //pega id empresa logada atual pela url
$id_laudo = $_GET["id"] ?? ""; //id do laudo pra excluir ou string vazia, por isso o ?? 
                        //para q, se existe e n é nula, = id, se não, = vazio stringue
//altera o valor da coluna ativo para nao, como forma de exclusão lógica
$sql = "UPDATE laudos SET ativo = 'nao' 
        WHERE id = '$id_laudo' AND id_empresa = '$id_empresa'";
if (mysqli_query($conect, $sql)) {
    header("Location: painel_empresa.php?cpf=" . $_GET["cpf"]); //redirecionar d volta para o painel da empresa ja mandando o cpf para ter a mostragem dos laudos relacionados ao mesmo paciente q  ja tava estava faendo a exlucsaão
    exit();
} else {
    echo "Erro ao excluir o laudo.";
}