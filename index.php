<?php
// Conexão direta com o banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'controle_gastos';

$conn = new mysqli($host, $user, $pass, $db);

// Verifica a conexão
if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou " . $conn->connect_error);
}

// Buscar meses
$sql_mes = "SELECT id, nome FROM mes";
$result_mes = $conn->query($sql_mes);

$meses = [];
if ($result_mes->num_rows > 0) {
    while ($row = $result_mes->fetch_assoc()) {
        $meses[] = $row;
    }
} else {
    echo "Nenhum mês encontrado.";
}

// Consultar para buscar entradas e saídas de cada mês
$sql_resumo = "SELECT m.id, m.nome AS mes, 
                      SUM(CASE WHEN t.tipo = 'Entrada' THEN t.valor ELSE 0 END) AS entradas, 
                      SUM(CASE WHEN t.tipo = 'Saída' THEN t.valor ELSE 0 END) AS saidas
               FROM movimentacoes t
               JOIN mes m ON t.mes_id = m.id
               GROUP BY m.id, m.nome";

$result_resumo = $conn->query($sql_resumo);

$dados_resumo = [];
if ($result_resumo->num_rows > 0) {
    while ($row = $result_resumo->fetch_assoc()) {
        $dados_resumo[] = $row;
    }
} else {
    echo "Nenhum dado de resumo encontrado.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Resumo Anual de Movimentações</h1>
        
        <!-- Exibição do gráfico -->
        <div id="graficoAnual"></div>

        <div class="row mt-5">
            <div class="col-md-12">
                <h4>Selecione um Mês para ver os detalhes</h4>
                <form action="detalhes_mes.php" method="GET" class="d-flex align-items-center">
                    <div class="me-3">
                        <select name="id" id="mes" class="form-select" required>
                            <option value="">Escolha o mês...</option>
                            <?php foreach ($meses as $mes) { ?>
                                <option value="<?= $mes['id'] ?>"><?= $mes['nome'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary me-3">Ver Detalhes</button>
                    <!-- Botão para abrir o modal de Adicionar Mês -->
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarMes">
                        Adicionar Mês
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Mês -->
    <div class="modal fade" id="modalAdicionarMes" tabindex="-1" aria-labelledby="modalAdicionarMesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAdicionarMesLabel">Adicionar Novo Mês</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="adicionar_mes.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome_mes" class="form-label">Nome do Mês</label>
                            <input type="text" class="form-control" id="nome_mes" name="nome_mes" placeholder="Ex: Janeiro" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Recebe os dados para o gráfico
        var dadosMensais = <?php echo json_encode($dados_resumo); ?>;

        // Extrai os meses, entradas e saídas
        var meses = dadosMensais.map(function(d) { return d.mes; });
        var entradas = dadosMensais.map(function(d) { return parseFloat(d.entradas); });
        var saidas = dadosMensais.map(function(d) { return parseFloat(d.saidas); });

        // Configuração do gráfico
        var options = {
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: meses
            },
            series: [
                {
                    name: 'Entradas',
                    data: entradas
                },
                {
                    name: 'Saídas',
                    data: saidas
                }
            ],
            colors: ['#28a745', '#dc3545'],
            title: {
                text: 'Movimentações Mensais'
            }
        };

        var chart = new ApexCharts(document.querySelector("#graficoAnual"), options);
        chart.render();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
