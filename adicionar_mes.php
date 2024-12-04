<?php
// Conexão com o banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'controle_gastos';

$conn = new mysqli($host, $user, $pass, $db);

// Verifica a conexão
if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou " . $conn->connect_error);
}

// Recebe o nome do mês do formulário
$nome_mes = $_POST['nome_mes'];

// Insere o novo mês no banco de dados
$sql = "INSERT INTO mes (nome) VALUES ('$nome_mes')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Mês adicionado com sucesso!'); window.location.href='index.php';</script>";
} else {
    echo "Erro ao adicionar mês: " . $conn->error;
}

$conn->close();
?>
