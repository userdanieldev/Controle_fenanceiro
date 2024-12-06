<?php
require_once('conexao.php');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Insere o novo mês
if (isset($_POST['nome_mes']) && !empty($_POST['nome_mes'])) {
    $nome_mes = $conn->real_escape_string($_POST['nome_mes']);
    $sql = "INSERT INTO mes (nome) VALUES ('$nome_mes')";
    if ($conn->query($sql) === TRUE) {
        // Retorna o ID do novo mês e o nome para o front-end
        echo json_encode([
            'id' => $conn->insert_id,
            'nome' => $nome_mes
        ]);
    } else {
        http_response_code(500);
        echo "Erro ao adicionar mês: " . $conn->error;
    }
} else {
    http_response_code(400);
    echo "Nome do mês não fornecido.";
}

$conn->close();
?>