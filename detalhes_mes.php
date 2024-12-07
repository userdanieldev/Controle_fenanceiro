<?php

session_start();
require_once('conexao.php');

if (isset($_POST['criar_transacao'])) {
    $nome = $_POST['txtNome'];
    $categoria = $_POST['txtCategoria'];
    $data = $_POST['txtData'];
    $tipo = $_POST['txtTipo'];
    $valor = $_POST['txtValor'];
    $mes_id = $_POST['id_mes'];

    $sql = "INSERT INTO movimentacoes (nome, categoria, data, tipo, valor, mes_id) VALUES ('$nome', '$categoria', '$data', '$tipo', '$valor', '$mes_id')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Erro ao salvar a transação: " . mysqli_error($conn);
    }
}

if (isset($_POST['editar_transacao'])) {
    $transacao_id = $_POST['id_movimentacao'];
    $nome = mysqli_real_escape_string($conn, $_POST['txtNome']);
    $categoria = mysqli_real_escape_string($conn, $_POST['txtCategoria']);
    $data = mysqli_real_escape_string($conn, $_POST['txtData']);
    $tipo = mysqli_real_escape_string($conn, $_POST['txtTipo']);
    $valor = mysqli_real_escape_string($conn, $_POST['txtValor']);

    $sql = "UPDATE movimentacoes SET nome = '{$nome}', categoria = '{$categoria}', data = '{$data}', tipo = '{$tipo}', valor = '{$valor}' WHERE id = {$transacao_id}";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Transação atualizada!";
        $_SESSION['type'] = 'success';
    } else {
        $_SESSION['message'] = "Erro ao atualizar a transação.";
        $_SESSION['type'] = 'error';
    }

    header("Location: index.php");
    exit();
}

// Consulta para buscar as movimentações do mês selecionado
$mes_id = $_GET['id']; // Pega o ID do mês a partir da URL
$sql_movimentacoes = "SELECT t.id, t.data, t.tipo, t.nome, t.valor, t.categoria FROM movimentacoes t WHERE t.mes_id = $mes_id";

$result_movimentacoes = $conn->query($sql_movimentacoes);

// Verifica se há resultados
$movimentacoes = [];
if ($result_movimentacoes->num_rows > 0) {
    while ($row = $result_movimentacoes->fetch_assoc()) {
        $movimentacoes[] = $row;
    }
} else {
    $movimentacoes = [];
}

// Consulta para buscar as entradas e saídas totais do mês
$sql_resumo = "SELECT SUM(CASE WHEN t.tipo = 'Entrada' THEN t.valor ELSE 0 END) AS entradas,
                      SUM(CASE WHEN t.tipo = 'Saída' THEN t.valor ELSE 0 END) AS saidas
               FROM movimentacoes t
               WHERE t.mes_id = $mes_id";

$result_resumo = $conn->query($sql_resumo);
$resumo = $result_resumo->fetch_assoc();

// Calculando o saldo final
$saldo_final = $resumo['entradas'] - $resumo['saidas'];

// Fechar a conexão
$conn->close();

$transacao = [
    'nome' => '',
    'categoria' => isset($_POST['txtCategoria']) ? $_POST['txtCategoria'] : '',  // Garantir que a categoria esteja definida
    'data' => '',
    'tipo' => '',
    'valor' => ''
];

// Verifique se há dados enviados (para edição)
if (isset($_POST['txtNome'])) {
    $transacao['nome'] = $_POST['txtNome'];
    $transacao['categoria'] = $_POST['txtCategoria'] ?? '';  // Usando o operador de coalescência nula
    $transacao['data'] = $_POST['txtData'];
    $transacao['tipo'] = $_POST['txtTipo'];
    $transacao['valor'] = $_POST['txtValor'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes das Movimentações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Detalhes das Movimentações</h1>
       
        <!-- Gráfico de barras horizontais com entradas, saídas e saldo -->
        <div>
            <canvas id="graficoMovimentacoes" class="mt-4"></canvas>
        </div>

        <!-- Tabela de movimentações -->
        <h4 class="mt-5">Movimentações</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentacoes as $movimentacao) { ?>
                    <tr>
                        <td><?= $movimentacao['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($movimentacao['data'])) ?></td>
                        <td><?= $movimentacao['tipo'] ?></td>
                        <td><?= $movimentacao['nome'] ?></td>
                        <td><?= $movimentacao['categoria'] ?></td>
                        <td>R$ <?= number_format($movimentacao['valor'], 2, ',', '.') ?></td>
                        <td><a href="#editarMovimentacaoModal_<?= $movimentacao['id'] ?>" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal">Editar</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    <div class="d-flex justify-content-between mt-4">
        <!-- Botão Voltar ao Index -->
        <a href="index.php" class="btn btn-danger">
            <i class="bi bi-arrow-left-circle-fill"></i> Voltar
        </a>

        <!-- Botão Adicionar Movimentação -->
        <a class="btn btn-success" href="#modalAdicionarMovimentacao?id=<?= $mes_id ?>" data-bs-toggle="modal" data-bs-target="#modalAdicionarMovimentacao">
            <i class="bi bi-plus-circle-fill"></i> Adicionar Movimentação
        </a>
    </div>


        <div class="modal fade" id="modalAdicionarMovimentacao" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content bg-dark">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5 text-light" id="exampleModalLabel">Adicionar Movimentação</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-light">
                        <form action="" method="POST">
                            <input type="hidden" name="id_mes" value="<?= $_GET['id'] ?>">
                            <div class="mb-4">
                                <label for="txtNome">Nome / Descrição</label>
                                <input type="text" name="txtNome" id="txtNome" value="<?= htmlspecialchars($transacao['nome']) ?>" class="form-control">
                            </div>
                            <div class="mb-4">
                                <label for="txtCategoria">Categoria</label>
                                <select name="txtCategoria" id="txtCategoria" class="form-control" required>
                                    <option selected disabled>Selecione uma categoria</option>
                                    <option class="text-danger" disabled>Categorias de Saída</option>
                                    <option value="Alimentação" <?= $transacao['categoria'] == 'Alimentação' ? 'selected' : '' ?>>Alimentação</option>
                                    <option value="Transporte" <?= $transacao['categoria'] == 'Transporte' ? 'selected' : '' ?>>Transporte</option>
                                    <option value="Lazer" <?= $transacao['categoria'] == 'Lazer' ? 'selected' : '' ?>>Lazer</option>
                                    <option value="Saúde" <?= $transacao['categoria'] == 'Saúde' ? 'selected' : '' ?>>Saúde</option>
                                    <option value="Compras" <?= $transacao['categoria'] == 'Compras' ? 'selected' : '' ?>>Compras</option>
                                    <option value="Outros" <?= $transacao['categoria'] == 'Outros' ? 'selected' : '' ?>>Educação</option>
                                    <option value="Aplicação em Investimentos" <?= $transacao['categoria'] == 'Aplicação em Investimentos' ? 'selected' : '' ?>>Aplicação em Investimentos</option>
                                    <option value="Serviços" <?= $transacao['categoria'] == 'Serviços' ? 'selected' : '' ?>>Serviços</option>
                                    <option class="text-success" disabled>Categorias de Entrada </option>
                                    <option value="Renda" <?= $transacao['categoria'] == 'Renda' ? 'selected' : '' ?>>Renda</option>
                                    <option value="Renda Extra" <?= $transacao['categoria'] == 'Renda Extra' ? 'selected' : '' ?>>Renda Extra</option>
                                    <option value="Rendimento de Investimentos" <?= $transacao['categoria'] == 'Rendimento de Investimentos' ? 'selected' : '' ?>>Rendimento de Investimentos</option>
                                    <option value="Doação" <?= $transacao['categoria'] == 'Doação' ? 'selected' : '' ?>>Doação</option>
                                    <option value="Prêmio" <?= $transacao['categoria'] == 'Prêmio' ? 'selected' : '' ?>>Prêmio</option>
                                    <option value="Outros" <?= $transacao['categoria'] == 'Outros' ? 'selected' : '' ?>>Outros</option>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="txtTipo">Tipo</label>
                                    <select name="txtTipo" id="txtTipo" class="form-control" required>
                                        <option class="text-success" value="Entrada" <?= $transacao['tipo'] == 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                                        <option class="text-danger" value="Saida" <?= $transacao['tipo'] == 'Saida' ? 'selected' : '' ?>>Saída</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="txtData">Data</label>
                                    <input type="date" name="txtData" id="txtData" value="<?= htmlspecialchars($transacao['data']) ?>" class="form-control">
                                    <small id="error-msg" class="text-danger"></small>
                                </div>
                                <div class="col">
                                    <label for="txtValor">Valor</label>
                                    <input type="number" name="txtValor" id="txtValor" value="<?= htmlspecialchars($transacao['valor']) ?>" class="form-control" step="any">
                                </div>
                            </div>
                            <div class="modal-footer mt-4">
                                <button type="submit" name="criar_transacao" class="btn btn-success">Salvar</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-circle-fill"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal do Editar Transação -->
        <?php foreach ($movimentacoes as $movimentacao) { ?>
            <div class="modal fade" id="editarMovimentacaoModal_<?= $movimentacao['id'] ?>" tabindex="-1" aria-labelledby="modalLabel_<?= $movimentacao['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-dark">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5 text-light" id="modalLabel_<?= $movimentacao['id'] ?>">Editar Movimentação</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-light">
                            <form action="" method="post">
                                <input type="hidden" name="id_movimentacao" value="<?= $movimentacao['id'] ?>">

                                <div class="mb-4">
                                    <label for="txtNome_<?= $movimentacao['id'] ?>">Nome / Descrição</label>
                                    <input type="text" name="txtNome" id="txtNome_<?= $movimentacao['id'] ?>" value="<?= htmlspecialchars($movimentacao['nome']) ?>" class="form-control">
                                </div>

                                <div class="mb-4">
                                    <label for="txtCategoria">Categoria</label>
                                    <select name="txtCategoria" id="txtCategoria" class="form-control" required>
                                        <option selected disabled>Selecione uma categoria</option>
                                        <option class="text-danger" disabled>Categorias de Saída</option>
                                        <option value="Alimentação" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Alimentação' ? 'selected' : '' ?>>Alimentação</option>
                                        <option value="Transporte" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Transporte' ? 'selected' : '' ?>>Transporte</option>
                                        <option value="Lazer" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Lazer' ? 'selected' : '' ?>>Lazer</option>
                                        <option value="Saúde" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Saúde' ? 'selected' : '' ?>>Saúde</option>
                                        <option value="Compras" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Compras' ? 'selected' : '' ?>>Compras</option>
                                        <option value="Outros" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Outros' ? 'selected' : '' ?>>Outros</option>
                                        <option value="Aplicação em Investimentos" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Aplicação em Investimentos' ? 'selected' : '' ?>>Aplicação em Investimentos</option>
                                        <option value="Serviços" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Serviços' ? 'selected' : '' ?>>Serviços</option>
                                        <option class="text-success" disabled>Categorias de Entrada</option>
                                        <option value="Renda" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Renda' ? 'selected' : '' ?>>Renda</option>
                                        <option value="Renda Extra" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Renda Extra' ? 'selected' : '' ?>>Renda Extra</option>
                                        <option value="Rendimento de Investimentos" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Rendimento de Investimentos' ? 'selected' : '' ?>>Rendimento de Investimentos</option>
                                        <option value="Doação" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Doação' ? 'selected' : '' ?>>Doação</option>
                                        <option value="Prêmio" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Prêmio' ? 'selected' : '' ?>>Prêmio</option>
                                        <option value="Outros" <?= isset($transacao['categoria']) && $transacao['categoria'] == 'Outros' ? 'selected' : '' ?>>Outros</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <label for="txtTipo_<?= $movimentacao['id'] ?>">Tipo</label>
                                        <select name="txtTipo" id="txtTipo_<?= $movimentacao['id'] ?>" class="form-control" required>
                                            <option value="Entrada" <?= $movimentacao['tipo'] == 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                                            <option value="Saida" <?= $movimentacao['tipo'] == 'Saida' ? 'selected' : '' ?>>Saída</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="txtData_<?= $movimentacao['id'] ?>">Data</label>
                                        <input type="date" name="txtData" id="txtData_<?= $movimentacao['id'] ?>" value="<?= htmlspecialchars($movimentacao['data']) ?>" class="form-control">
                                    </div>
                                    <div class="col">
                                        <label for="txtValor_<?= $movimentacao['id'] ?>">Valor</label>
                                        <input type="number" name="txtValor" id="txtValor_<?= $movimentacao['id'] ?>" value="<?= htmlspecialchars($movimentacao['valor']) ?>" class="form-control" step="any">
                                    </div>
                                </div>

                                <div class="modal-footer mt-4">
                                    <button type="submit" name="editar_transacao" class="btn btn-success">Salvar</button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>



        <script>
            // Dados para o gráfico
            var entradas = <?php echo $resumo['entradas']; ?>;
            var saidas = <?php echo $resumo['saidas']; ?>;
            var saldo_final = <?php echo $saldo_final; ?>;

            // Configuração do gráfico de barras horizontais
            var ctx = document.getElementById('graficoMovimentacoes').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'bar', // Tipo do gráfico
                data: {
                    labels: ['Entradas', 'Saídas', 'Saldo'], // Labels
                    datasets: [{
                        label: 'Movimentações',
                        data: [entradas, saidas, saldo_final], // Dados das entradas, saídas e saldo
                        backgroundColor: ['rgba(40, 167, 69, 0.6)', 'rgba(220, 53, 69, 0.6)', 'rgba(0, 123, 255, 0.6)'], // Cores das barras
                        borderColor: ['rgba(40, 167, 69, 1)', 'rgba(220, 53, 69, 1)', 'rgba(0, 123, 255, 1)'], // Cores das bordas
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y', // Mudando a orientação para barras horizontais
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Valor (R$)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Movimentações'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Entradas, Saídas e Saldo Final'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.dataset.label + ': R$ ' + tooltipItem.raw.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>

</html>