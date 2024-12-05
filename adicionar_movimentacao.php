<?php

session_start();
require_once('conexao.php');

$conn = new mysqli($host, $user, $pass, $db);

// realiza a verificação de conexao com com o banco
if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou : " . $conn->connect_error);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mes_id = $_POST['mes_id'];
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];

    // Prepara a consulta para inserir os dados na tabela 'movimentacoes'
    $sql = "INSERT INTO movimentacoes (mes_id, nome, tipo, categoria valor, data) VALUES (?, ?, ?, ?, ?, ?)";

    // Usando prepared statement para prevenir SQL injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issds", $mes_id, $nome, $tipo, $valor, $data);

    if ($stmt->execute()) {
        // Se a inserção for bem-sucedida, redireciona para a página de detalhes do mês
        header("Location: detalhes_mes.php?id=" . $mes_id);
        exit();
    } else {
        // Se ocorrer um erro, exibe uma mensagem de erro
        echo "Erro ao adicionar movimentação: " . $stmt->error;
    }
}

// Fechar a conexão
$conn->close();
?>
