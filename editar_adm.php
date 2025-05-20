<?php
session_start(); //manter sessão do adm
include "conecta.php";

//verifica se está logado como administrador
if (!isset($_SESSION['adm_id']) || empty($_SESSION['adm_id'])) {
    header("Location: index.php");
    exit(); //se não antes de ler qualquer coisa ja retorna para o index, pois foi ssfado tentou entrar urla
}

$tipo = $_GET['tipo'] ?? ""; //usuario ou empresa
$id_original = $_GET['id'] ?? ""; //cpf ou id (original)
$mensagem = ""; //mesangem para gravar futuras mensagens... ?? para caso nãoexistir, mantem vazio

// Se for POST, é pq envio pra edição... atualiza
if ($_SERVER["REQUEST_METHOD"] === "POST" and isset($_POST['editar'])) {
    $tipo = $_POST['tipo'];
    $id_original = $_POST['id_original'];
    $id_novo = $_POST['id']; // pode ser o novo CPF ou novo ID
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $login = $_POST['login'];
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];
    $ativo = $_POST['ativo'] ?? 'sim';
    $senha = $_POST['senha'];

    if ($tipo === "usuario") { //se for usuario tem data de nascimento
        $data_nascimento = $_POST['data_nascimento'];

        if (!empty($senha)) { //se parte senha diferente de vazio...
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            mysqli_query($conect, "UPDATE usuarios SET cpf='$id_novo', nome='$nome', email='$email', login='$login', telefone='$telefone', endereco='$endereco', data_nascimento='$data_nascimento', ativo='$ativo', senha='$senha_hash' WHERE cpf='$id_original'");
        } else { //se não so atualiza tudo menos esnha
            mysqli_query($conect, "UPDATE usuarios SET cpf='$id_novo', nome='$nome', email='$email', login='$login', telefone='$telefone', endereco='$endereco', data_nascimento='$data_nascimento', ativo='$ativo' WHERE cpf='$id_original'");
        }

    } elseif ($tipo === "empresa") { //se for uma emoresa
        if (!empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            mysqli_query($conect, "UPDATE empresas SET id='$id_novo', nome='$nome', email='$email', login='$login', telefone='$telefone', endereco='$endereco', ativo='$ativo', senha='$senha_hash' WHERE id='$id_original'");
        } else {
            mysqli_query($conect, "UPDATE empresas SET id='$id_novo', nome='$nome', email='$email', login='$login', telefone='$telefone', endereco='$endereco', ativo='$ativo' WHERE id='$id_original'");
        }  //mesmas coisas só q sem o data nascimento q nn existe pra emrpesa
    }

    header("Location: painel_adm.php");
    exit(); //apóes encerra aqui e volta pro painel do adm
}

// Se for GET, busca os dados atuais para prencher form
$registro = null; //para guradar oq vai vir do banco
if ($tipo === "usuario") { //s do tipo usuario, bsca porcpf
    $res = mysqli_query($conect, "SELECT * FROM usuarios WHERE cpf = '$id_original'");
    $registro = mysqli_fetch_assoc($res);
} elseif ($tipo === "empresa") { //se empresa por id
    $res = mysqli_query($conect, "SELECT * FROM empresas WHERE id = '$id_original'");
    $registro = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar <?= ucfirst($tipo) //titulo dinamico para ficar editar 'tal coisa' ?></title>
    <link rel="stylesheet" href="estilos_cseses/editar_adm.css">
    <script>
        function confirmarEdicao() { //confirma exclusão antes de enviar ela..
            return confirm("Deseja salvar as alterações?");
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Editar <?= ucfirst($tipo) ?></h2>

    <?php if ($registro): //se existir registro... form post para ter a diferenciação e php funciona de alguma forma?>
        <form method="POST" onsubmit="return confirmarEdicao();">
            <input type="hidden" name="tipo" value="<?= $tipo // quando submetido, escript pop up pra confirmar... ?>">
            <input type="hidden" name="id_original" value="<?= $id_original  //campo escondido de tipo e id originals?>">

            <?php if ($tipo === "usuario"): ?>
                <input type="text" name="id" value="<?= $registro['cpf'] ?>" placeholder="CPF" required><br><br>
            <?php else: ?>
                <input type="number" name="id" value="<?= $registro['id'] ?>" placeholder="ID" required><br><br>
            <?php endif; //para colocar CPF ou ID dependendo do tipoq veio pra editar..?>

            <input type="text" name="nome" value="<?= $registro['nome'] ?>" placeholder="Nome" required><br><br>
            <input type="email" name="email" value="<?= $registro['email'] ?>" placeholder="Email" required><br><br>
            <input type="text" name="login" value="<?= $registro['login'] ?>" placeholder="Login" required><br><br>
            <input type="text" name="telefone" value="<?= $registro['telefone'] ?? '' ?>" placeholder="Telefone"><br><br>
            <input type="text" name="endereco" value="<?= $registro['endereco'] ?? '' ?>" placeholder="Endereço"><br><br>

            <?php if ($tipo === "usuario"): //se usuario data de nascimento tbm?>
                <input type="date" name="data_nascimento" value="<?= $registro['data_nascimento'] ?>"><br><br>
            <?php endif; ?>

            <input type="password" name="senha" placeholder="Nova Senha (opcional)"><br><br>

            <label>Status: 
                <select name="ativo">
                    <option value="sim" <?= ($registro['ativo'] === 'sim') ? 'selected' : '' ?>>Ativo</option>
                    <option value="nao" <?= ($registro['ativo'] === 'nao') ? 'selected' : '' ?>>Inativo</option>
                </select>
            </label><br><br>

            <button type="submit" name="editar" class="btn">Salvar Alterações</button>
        </form>
    <?php else: ?>
        <p class="erro">Registro não encontrado.</p>
    <?php endif; ?>
    <a href="painel_adm.php" class="btn-voltar">Voltar ao Painel</a>
</div>
</body>
</html>