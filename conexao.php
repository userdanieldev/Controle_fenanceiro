<?php

// conexap direta com banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'controle_gastos';

$conn = new mysqli($host, $user, $pass, $db);

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou : " . $conn->connect_error);
}

?>