<?php
require_once('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_mes'])) {
    $id_mes = intval($_POST['id_mes']);

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("A conexão com o banco de dados falhou " . $conn->connect_error);
    }

    // Excluir o mês e suas movimentações relacionadas
    $sql = "DELETE FROM movimentacoes WHERE mes_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_mes);
    $stmt->execute();

    $sql_mes = "DELETE FROM mes WHERE id = ?";
    $stmt_mes = $conn->prepare($sql_mes);
    $stmt_mes->bind_param("i", $id_mes);
    $stmt_mes->execute();

    $stmt->close();
    $stmt_mes->close();
    $conn->close();

    header("Location: index.php"); 
    exit;
}
?>
