<?php
session_start();//sessão paramantervariaveis adm logada
include "conecta.php";

//verifica se está logado como administrador
if (!isset($_SESSION['adm_id']) || empty($_SESSION['adm_id'])) { //se não existir ou estar vazio...
    header("Location: index.php"); //tento vir direto pelaurl, retorna poindex
    exit();//ja encerra pra nada poder ser carregado do painel
}
//variaveis para uso dps
$cpf_laudos_usuario = $_GET['cpf_laudos'] ?? ""; //se for passado essa info pela url vai armazenar, enquantonão, deixa como estringvazia
$laudos_sem_cpf = isset($_GET['sem_cpf']); //verifica se exite o paramentro sem cpf

//logica para aexclusãopermanente que o adm podefazer, tendo seusdiferentes casos...
if (isset($_GET['excluir'], $_GET['tipo'], $_GET['id'])) {//se existir excluir, com um tipo e id..
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];

    if ($tipo === 'laudo') {
        mysqli_query($conect, "DELETE FROM laudos WHERE id = '$id'");
    } elseif ($tipo === 'usuario') {
        mysqli_query($conect, "DELETE FROM usuarios WHERE cpf = '$id'");
    } elseif ($tipo === 'empresa') {
        mysqli_query($conect, "DELETE FROM empresas WHERE id = '$id'");
    }
//diferentes exclusões possiveis
    header("Location: painel_adm.php");
    exit(); //volta a ler o painel, e encerra essa parte
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador</title>
    <link rel="stylesheet" href="estilos_cseses/painel_adm.css">
    <script>
        function confirmarExclusao(tipo, id) { //função que recebe o tipo e id...
            if (confirm("Tem certeza que deseja excluir permanentemente este " + tipo + "?")) { //conforme o tipo, o pop up de confirm exclu seadapta..
                window.location.href = "painel_adm.php?excluir=1&tipo=" + tipo + "&id=" + id;
            } //se confirmado... envia parametros excluir, tipo e idpela url... oq aciona o exclusão que esta verificando se existe essas 3coisas ou não, e no fim do php de exclusão, encerra pra não ficar infinito
        }
        function verLaudos(cpf) {
            window.location.href = 'painel_adm.php?cpf_laudos=' + cpf;
        }   //redireciona a propria paginasó q com  os laudos do cpf informado para a tela... envio dissopela url padrão
        function editarRegistro(tipo, id) {
            window.location.href = 'editar_adm.php?tipo=' + tipo + '&id=' + id;
        } //função para levar para a agina de ddição, mandando o tipo"usuario ou empresa" e o id, para la resgatar todos os dados do banco e editar..
        function verSemCpf() {
            window.location.href = 'painel_adm.php?sem_cpf=1';
        }   //caso de ter laudos  sem cpf vinculado, opção paramostrar eles na tela como os com cpf
    </script>
</head>
<body>
<div class="container">

    <div style="margin-bottom: 20px;">
        <button class="btn" onclick="verSemCpf()">Laudos sem CPF</button>
    </div>

    <?php if (!empty($cpf_laudos_usuario)) ://quando passado pela url atraves da função, isso diferente de vazio... cria tabela para mostrar os laudos vinculados a mostrar o cpf de um usuario ?>
        <h2>Laudos do CPF: <?= $cpf_laudos_usuario ?></h2>
        <table>
            <tr><th>CPF</th><th>Empresa</th><th>Nome Arquivo</th><th>Data</th><th>Ver</th><th>Excluir</th></tr>
            <?php
            $laudos = mysqli_query($conect, "SELECT l.id, l.cpf, l.nome_arquivo, l.data_envio, e.login AS empresa 
                                             FROM laudos l 
                                             LEFT JOIN empresas e ON l.id_empresa = e.id 
                                             WHERE l.cpf = '$cpf_laudos_usuario'");
            while ($l = mysqli_fetch_assoc($laudos)) { //consulta para trazer laudos com aquele cpf... e exibição de tudo na tabela... com o ?? que seempresa for string vazia, retorna ela como desconhecida
                echo "<tr>
                    <td>{$l['cpf']}</td>
                    <td>" . ($l['empresa'] ?? 'Desconhecida') . "</td>
                    <td>{$l['nome_arquivo']}</td>
                    <td>" . date("d/m/Y H:i", strtotime($l['data_envio'])) . "</td>
                    <td><a class='btn' target='_blank' href='baixar_laudo_adm.php?id={$l['id']}'>Ver</a></td>
                    <td><button class='btn' onclick=\"confirmarExclusao('laudo', {$l['id']})\">Excluir</button></td>
                </tr>";
            }
            ?>
        </table>
    <?php endif; //acima notão excluirchama função q faz tudo,e o baixar manda pro arquivo q faz tudo tbm?>

    <?php if ($laudos_sem_cpf): //se existir paramentro na url para laudos sem cpf...?>
        <h2>Laudos sem CPF vinculado</h2>
        <table>
            <tr><th>CPF</th><th>Empresa</th><th>Nome Arquivo</th><th>Data</th><th>Ver</th><th>Excluir</th></tr>
            <?php
            $laudos = mysqli_query($conect, "SELECT l.id, l.cpf, l.nome_arquivo, l.data_envio, e.login AS empresa 
                                             FROM laudos l 
                                             LEFT JOIN empresas e ON l.id_empresa = e.id 
                                             WHERE l.cpf IS NULL OR l.cpf = ''");
            while ($l = mysqli_fetch_assoc($laudos)) { //busca todos os laudos ao qual o cpf é vazio... com a opção de tbm nn estar referenciado a nenhuma emprssa
                echo "<tr>
                    <td>--</td>
                    <td>" . ($l['empresa'] ?? 'Desconhecida') . "</td>
                    <td>{$l['nome_arquivo']}</td>
                    <td>" . date("d/m/Y H:i", strtotime($l['data_envio'])) . "</td>
                    <td><a class='btn' target='_blank' href='baixar_laudo_adm.php?id={$l['id']}'>Ver</a></td>
                    <td><button class='btn' onclick=\"confirmarExclusao('laudo', {$l['id']})\">Excluir</button></td>
                </tr>";
            }
            ?>
        </table>
    <?php endif; //mesmos botões de ação e endif pra acabar..?>

    <h2>Usuários</h2>
    <table>
        <tr><th>Nome</th><th>Ações</th></tr>
        <?php //tabela que busca tudo para mostrar os usuarios... sem condição pois esta vai estarsempre de padrão desde quando abrir
        $usuarios = mysqli_query($conect, "SELECT cpf, nome FROM usuarios");
        while ($u = mysqli_fetch_assoc($usuarios)) {
            echo "<tr>
                <td>{$u['nome']}</td>
                <td>
                    <button class='btn' onclick=\"editarRegistro('usuario', '{$u['cpf']}')\">Editar</button>
                    <button class='btn' onclick=\"confirmarExclusao('usuario', '{$u['cpf']}')\">Excluir</button>
                    <button class='btn' onclick=\"verLaudos('{$u['cpf']}')\">Laudos</button>
                </td>
            </tr>";
        }
        //mostra nomes... e ações q enviam tudo q precisa para suas respectivas funções ou  arquivos que fazem o preocesso?>
    </table>

    <h2>Empresas</h2>
    <table>
        <tr><th>Nome</th><th>Ações</th></tr>
        <?php //mesmo lógica doq aconteceu para os usuarios...
        $empresas = mysqli_query($conect, "SELECT id, nome FROM empresas");
        while ($e = mysqli_fetch_assoc($empresas)) {
            echo "<tr>
                <td>{$e['nome']}</td>
                <td>
                    <button class='btn' onclick=\"editarRegistro('empresa', {$e['id']})\">Editar</button>
                    <button class='btn' onclick=\"confirmarExclusao('empresa', {$e['id']})\">Excluir</button>
                </td>
            </tr>";
        }
        ?>
    </table>

    <div class="botoes-topo">
        <a href="logaut.php" class="btn-perfil">Sair</a>
    </div>
</div>
</body>
</html>