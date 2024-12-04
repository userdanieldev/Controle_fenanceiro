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

// Consulta para buscar as movimentações do mês selecionado
$mes_id = $_GET['id']; // Pega o ID do mês a partir da URL
$sql_movimentacoes = "SELECT t.id, t.data, t.tipo, t.nome, t.valor FROM movimentacoes t WHERE t.mes_id = $mes_id";

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes das Movimentações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Detalhes das Movimentações</h1>

        <!-- Resumo -->
        <div class="row">
            <div class="col-md-6">
                <h5>Entradas: R$ <?= number_format($resumo['entradas'], 2, ',', '.') ?></h5>
            </div>
            <div class="col-md-6">
                <h5>Saídas: R$ <?= number_format($resumo['saidas'], 2, ',', '.') ?></h5>
            </div>
        </div>

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
                    <th>Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movimentacoes as $movimentacao) { ?>
                    <tr>
                        <td><?= $movimentacao['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($movimentacao['data'])) ?></td>
                        <td><?= $movimentacao['tipo'] ?></td>
                        <td><?= $movimentacao['nome'] ?></td>
                        <td>R$ <?= number_format($movimentacao['valor'], 2, ',', '.') ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Botão para adicionar movimentação -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarMovimentacao">Adicionar Movimentação</button>

        <!-- Modal -->
        <div class="modal fade" id="modalAdicionarMovimentacao" tabindex="-1" aria-labelledby="modalAdicionarMovimentacaoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAdicionarMovimentacaoLabel">Adicionar Movimentação</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="adicionar_movimentacao.php">
                            <div class="mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-control" id="tipo" name="tipo" required>
                                    <option value="Entrada">Entrada</option>
                                    <option value="Saída">Saída</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="valor" class="form-label">Valor</label>
                                <input type="number" class="form-control" id="valor" name="valor" required>
                            </div>
                            <div class="mb-3">
                                <label for="data" class="form-label">Data</label>
                                <input type="date" class="form-control" id="data" name="data" required>
                            </div>
                            <input type="hidden" name="mes_id" value="<?= $mes_id ?>"/>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dados para o gráfico
        var entradas = <?php echo $resumo['entradas']; ?>;
        var saidas = <?php echo $resumo['saidas']; ?>;
        var saldo_final = <?php echo $saldo_final; ?>;

        // Configuração do gráfico de barras horizontais
        var ctx = document.getElementById('graficoMovimentacoes').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',  // Tipo do gráfico
            data: {
                labels: ['Entradas', 'Saídas', 'Saldo'],  // Labels
                datasets: [{
                    label: 'Movimentações',
                    data: [entradas, saidas, saldo_final],  // Dados das entradas, saídas e saldo
                    backgroundColor: ['rgba(40, 167, 69, 0.6)', 'rgba(220, 53, 69, 0.6)', 'rgba(0, 123, 255, 0.6)'],  // Cores das barras
                    borderColor: ['rgba(40, 167, 69, 1)', 'rgba(220, 53, 69, 1)', 'rgba(0, 123, 255, 1)'],  // Cores das bordas
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',  // Mudando a orientação para barras horizontais
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

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
