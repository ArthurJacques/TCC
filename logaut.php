<?php
session_start();
session_unset(); // limpa todas as variáveis de sessão
session_destroy(); // encerra a sessão
header("Location: index.php"); // redireciona para o início
exit();