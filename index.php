<?php

session_start();
require_once('conexao.php');

$conn = new mysqli($host, $user, $pass, $db);

// realiza a verificação de conexao com com o banco
if ($conn->connect_error) {
    die("A conexão com o banco de dados falhou : " . $conn->connect_error);
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

// Consultar resumo anual (entradas, saídas e saldo)
$sql_resumo_anual = "SELECT 
                        SUM(CASE WHEN t.tipo = 'Entrada' THEN t.valor ELSE 0 END) AS total_entradas, 
                        SUM(CASE WHEN t.tipo = 'Saída' THEN t.valor ELSE 0 END) AS total_saidas
                     FROM movimentacoes t";
$result_resumo_anual = $conn->query($sql_resumo_anual);

$resumo_anual = $result_resumo_anual->fetch_assoc();
$total_entradas = $resumo_anual['total_entradas'] ?? 0;
$total_saidas = $resumo_anual['total_saidas'] ?? 0;
$saldo_final_anual = $total_entradas - $total_saidas;

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
    <style>
        /* Estilo padrão dos cards */
        .card-resumo {
            transition: all 0.3s ease-in-out;
            border: 2px solid transparent;
        }

        /* Efeito ao passar o mouse */
        .card-resumo:hover {
            border-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            transform: scale(1.05); /* Leve aumento no tamanho */
        }
        #mes {
            max-width: 100%; /* Largura do select igual ao gráfico */
        }

        .d-flex.gap-3 button {
             width: auto; /* Mantém o tamanho padrão */
        }

    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Resumo Anual de Movimentações</h1>
        
        <!-- Resumo em Cards -->
        <div class="row text-center mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3 card-resumo">
                    <div class="card-body">
                        <h5 class="card-title">Total de Entradas</h5>
                        <p class="card-text">R$ <?= number_format($total_entradas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3 card-resumo">
                    <div class="card-body">
                        <h5 class="card-title">Total de Saídas</h5>
                        <p class="card-text">R$ <?= number_format($total_saidas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3 card-resumo">
                    <div class="card-body">
                        <h5 class="card-title">Saldo Final</h5>
                        <p class="card-text">R$ <?= number_format($saldo_final_anual, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exibição do gráfico -->
        <div id="graficoAnual"></div>

        <div class="row mt-5">
    <div class="col-md-12 text-center">
        <h4>Selecione um Mês para ver os detalhes</h4>
        <form action="detalhes_mes.php" method="GET">
            <!-- Select com largura total -->
            <div class="mb-4">
                <select name="id" id="mes" class="form-select w-100" required>
                    <option value="">Escolha o mês...</option>
                    <?php foreach ($meses as $mes) { ?>
                        <option value="<?= $mes['id'] ?>"><?= $mes['nome'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <!-- Botões centralizados -->
            <div class="d-flex justify-content-center gap-3">
                <button type="submit" class="btn btn-primary">Ver Detalhes</button>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAdicionarMes">
                    Adicionar Mês
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalExcluirMes">
                    Excluir Mês
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Adicionar Mês -->
<div class="modal fade" id="modalAdicionarMes" tabindex="-1" aria-labelledby="modalAdicionarMesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white text-white" id="modalAdicionarMesLabel">Adicionar Novo Mês</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="adicionar_mes.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome_mes" class="form-label text-white">Nome do Mês</label>
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

<!-- Modal para Excluir Mês -->
<div class="modal fade" id="modalExcluirMes" tabindex="-1" aria-labelledby="modalExcluirMesLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="modalExcluirMesLabel">Excluir Mês</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="excluir_mes.php" method="POST">
                <div class="modal-body text-white">
                    <p>Tem certeza de que deseja excluir o mês selecionado?</p>
                    <input type="hidden" id="id_mes_excluir" name="id_mes" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Atualiza o ID do mês no modal de exclusão
    const mesSelect = document.getElementById('mes');
    const idMesExcluir = document.getElementById('id_mes_excluir');

    mesSelect.addEventListener('change', function () {
        idMesExcluir.value = this.value;
    });

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
