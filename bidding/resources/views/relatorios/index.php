<?php
$content = '
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Relatórios</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportarPDF">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExportarExcel">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Filtros</h5>
            </div>
            <div class="card-body">
                <form id="formFiltros">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tipoRelatorio" class="form-label">Tipo de Relatório</label>
                                <select class="form-select" id="tipoRelatorio" name="tipoRelatorio">
                                    <option value="licitacoesPorPeriodo">Licitações por Período</option>
                                    <option value="propostasPorStatus">Propostas por Status</option>
                                    <option value="desempenhoPorCliente">Desempenho por Cliente</option>
                                    <option value="licitacoesPorUF">Licitações por UF</option>
                                    <option value="valorMedioPorModalidade">Valor Médio por Modalidade</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dataInicio" class="form-label">Data Início</label>
                                <input type="date" class="form-control" id="dataInicio" name="data_inicio" value="<?php echo date("Y-m-01"); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dataFim" class="form-label">Data Fim</label>
                                <input type="date" class="form-control" id="dataFim" name="data_fim" value="<?php echo date("Y-m-d"); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" id="btnGerarRelatorio">
                            <i class="fas fa-chart-bar"></i> Gerar Relatório
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="resultadoRelatorio" style="display: none;">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title" id="tituloRelatorio">Resultado do Relatório</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <canvas id="chartRelatorio" height="300"></canvas>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-sm" id="tabelaRelatorio">
                            <thead id="cabecalhoTabela">
                                <!-- Cabeçalho da tabela será preenchido dinamicamente -->
                            </thead>
                            <tbody id="corpoTabela">
                                <!-- Corpo da tabela será preenchido dinamicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
';

$scripts = '
<script>
$(document).ready(function() {
    var chartRelatorio = null;

    // Manipular clique no botão Gerar Relatório
    $("#btnGerarRelatorio").click(function() {
        var tipoRelatorio = $("#tipoRelatorio").val();
        var dataInicio = $("#dataInicio").val();
        var dataFim = $("#dataFim").val();

        // Destruir gráfico anterior se existir
        if (chartRelatorio != null) {
            chartRelatorio.destroy();
        }

        // Chamada AJAX para gerar o relatório
        $.ajax({
            url: "/relatorios/" + tipoRelatorio,
            type: "GET",
            data: {
                data_inicio: dataInicio,
                data_fim: dataFim
            },
            dataType: "json",
            success: function(response) {
                // Mostrar resultado
                $("#resultadoRelatorio").show();

                // Definir título do relatório
                $("#tituloRelatorio").text(getTituloRelatorio(tipoRelatorio));

                // Gerar gráfico e tabela de acordo com o tipo de relatório
                switch (tipoRelatorio) {
                    case "licitacoesPorPeriodo":
                        gerarRelatorioLicitacoesPorPeriodo(response);
                        break;
                    case "propostasPorStatus":
                        gerarRelatorioPropostasPorStatus(response);
                        break;
                    case "desempenhoPorCliente":
                        gerarRelatorioDesempenhoPorCliente(response);
                        break;
                    case "licitacoesPorUF":
                        gerarRelatorioLicitacoesPorUF(response);
                        break;
                    case "valorMedioPorModalidade":
                        gerarRelatorioValorMedioPorModalidade(response);
                        break;
                }
            },
            error: function(xhr, status, error) {
                alert("Erro ao gerar relatório: " + error);
            }
        });
    });

    // Função para obter o título do relatório
    function getTituloRelatorio(tipoRelatorio) {
        switch (tipoRelatorio) {
            case "licitacoesPorPeriodo":
                return "Licitações por Período";
            case "propostasPorStatus":
                return "Propostas por Status";
            case "desempenhoPorCliente":
                return "Desempenho por Cliente";
            case "licitacoesPorUF":
                return "Licitações por UF";
            case "valorMedioPorModalidade":
                return "Valor Médio por Modalidade";
            default:
                return "Relatório";
        }
    }

    // Funções para gerar cada tipo de relatório
    function gerarRelatorioLicitacoesPorPeriodo(data) {
        // Preparar dados para o gráfico
        var labels = Object.keys(data.licitacoesPorDia);
        var valores = Object.values(data.licitacoesPorDia);

        // Gerar gráfico
        var ctx = document.getElementById("chartRelatorio").getContext("2d");
        chartRelatorio = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "Quantidade de Licitações",
                    data: valores,
                    backgroundColor: "rgba(0, 123, 255, 0.5)",
                    borderColor: "rgba(0, 123, 255, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Gerar tabela
        var cabecalho = "<tr><th>Data</th><th>Quantidade</th></tr>";
        $("#cabecalhoTabela").html(cabecalho);

        var corpo = "";
        for (var i = 0; i < labels.length; i++) {
            corpo += "<tr><td>" + formatarData(labels[i]) + "</td><td>" + valores[i] + "</td></tr>";
        }
        $("#corpoTabela").html(corpo);
    }

    function gerarRelatorioPropostasPorStatus(data) {
        // Preparar dados para o gráfico
        var labels = [];
        var valores = [];
        var cores = [];

        $.each(data, function(index, item) {
            labels.push(getStatusLabel(item.status));
            valores.push(item.total);
            cores.push(getStatusColor(item.status));
        });

        // Gerar gráfico
        var ctx = document.getElementById("chartRelatorio").getContext("2d");
        chartRelatorio = new Chart(ctx, {
            type: "pie",
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: cores
                }]
            },
            options: {
                responsive: true
            }
        });

        // Gerar tabela
        var cabecalho = "<tr><th>Status</th><th>Quantidade</th></tr>";
        $("#cabecalhoTabela").html(cabecalho);

        var corpo = "";
        for (var i = 0; i < labels.length; i++) {
            corpo += "<tr><td>" + labels[i] + "</td><td>" + valores[i] + "</td></tr>";
        }
        $("#corpoTabela").html(corpo);
    }

    function gerarRelatorioDesempenhoPorCliente(data) {
        // Preparar dados para o gráfico
        var labels = [];
        var vencedoras = [];
        var rejeitadas = [];
        var aceitas = [];

        $.each(data, function(index, item) {
            labels.push(item.nome);
            vencedoras.push(item.vencedoras);
            rejeitadas.push(item.rejeitadas);
            aceitas.push(item.aceitas);
        });

        // Gerar gráfico
        var ctx = document.getElementById("chartRelatorio").getContext("2d");
        chartRelatorio = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Vencedoras",
                        data: vencedoras,
                        backgroundColor: "rgba(40, 167, 69, 0.5)",
                        borderColor: "rgba(40, 167, 69, 1)",
                        borderWidth: 1
                    },
                    {
                        label: "Rejeitadas",
                        data: rejeitadas,
                        backgroundColor: "rgba(220, 53, 69, 0.5)",
                        borderColor: "rgba(220, 53, 69, 1)",
                        borderWidth: 1
                    },
                    {
                        label: "Aceitas",
                        data: aceitas,
                        backgroundColor: "rgba(0, 123, 255, 0.5)",
                        borderColor: "rgba(0, 123, 255, 1)",
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Gerar tabela
        var cabecalho = "<tr><th>Cliente</th><th>Total Propostas</th><th>Vencedoras</th><th>Rejeitadas</th><th>Aceitas</th><th>Valor Total</th></tr>";
        $("#cabecalhoTabela").html(cabecalho);

        var corpo = "";
        $.each(data, function(index, item) {
            corpo += "<tr><td>" + item.nome + "</td><td>" + item.total_propostas + "</td><td>" + item.vencedoras + "</td><td>" + item.rejeitadas + "</td><td>" + item.aceitas + "</td><td>R$ " + formatarValor(item.valor_total) + "</td></tr>";
        });
        $("#corpoTabela").html(corpo);
    }

    function gerarRelatorioLicitacoesPorUF(data) {
        // Preparar dados para o gráfico
        var labels = [];
        var valores = [];

        $.each(data, function(index, item) {
            labels.push(item.uf);
            valores.push(item.total);
        });

        // Gerar gráfico
        var ctx = document.getElementById("chartRelatorio").getContext("2d");
        chartRelatorio = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Quantidade de Licitações",
                    data: valores,
                    backgroundColor: "rgba(0, 123, 255, 0.5)",
                    borderColor: "rgba(0, 123, 255, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Gerar tabela
        var cabecalho = "<tr><th>UF</th><th>Quantidade</th></tr>";
        $("#cabecalhoTabela").html(cabecalho);

        var corpo = "";
        $.each(data, function(index, item) {
            corpo += "<tr><td>" + item.uf + "</td><td>" + item.total + "</td></tr>";
        });
        $("#corpoTabela").html(corpo);
    }

    function gerarRelatorioValorMedioPorModalidade(data) {
        // Preparar dados para o gráfico
        var labels = [];
        var valores = [];

        $.each(data, function(index, item) {
            labels.push(item.modalidade_nome);
            valores.push(item.valor_medio);
        });

        // Gerar gráfico
        var ctx = document.getElementById("chartRelatorio").getContext("2d");
        chartRelatorio = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    label: "Valor Médio (R$)",
                    data: valores,
                    backgroundColor: "rgba(0, 123, 255, 0.5)",
                    borderColor: "rgba(0, 123, 255, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

// Gerar tabela
var cabecalho = "<tr><th>Modalidade</th><th>Valor Médio</th><th>Quantidade</th></tr>";
        $("#cabecalhoTabela").html(cabecalho);

        var corpo = "";
        $.each(data, function(index, item) {
            corpo += "<tr><td>" + item.modalidade_nome + "</td><td>R$ " + formatarValor(item.valor_medio) + "</td><td>" + item.total + "</td></tr>";
        });
        $("#corpoTabela").html(corpo);
    }

    // Funções auxiliares
    function formatarData(data) {
        var partes = data.split("-");
        return partes[2] + "/" + partes[1] + "/" + partes[0];
    }

    function formatarValor(valor) {
        return parseFloat(valor).toLocaleString("pt-BR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getStatusLabel(status) {
        switch (status) {
            case "elaboracao":
                return "Em Elaboração";
            case "enviada":
                return "Enviada";
            case "aceita":
                return "Aceita";
            case "rejeitada":
                return "Rejeitada";
            case "vencedora":
                return "Vencedora";
            default:
                return "Desconhecido";
        }
    }

    function getStatusColor(status) {
        switch (status) {
            case "elaboracao":
                return "rgba(108, 117, 125, 0.7)"; // Cinza
            case "enviada":
                return "rgba(0, 123, 255, 0.7)"; // Azul
            case "aceita":
                return "rgba(40, 167, 69, 0.7)"; // Verde
            case "rejeitada":
                return "rgba(220, 53, 69, 0.7)"; // Vermelho
            case "vencedora":
                return "rgba(255, 193, 7, 0.7)"; // Amarelo
            default:
                return "rgba(108, 117, 125, 0.7)"; // Cinza
        }
    }

    // Manipular clique no botão Exportar PDF
    $("#btnExportarPDF").click(function() {
        window.print();
    });

    // Manipular clique no botão Exportar Excel
    $("#btnExportarExcel").click(function() {
        var tipoRelatorio = $("#tipoRelatorio").val();
        var dataInicio = $("#dataInicio").val();
        var dataFim = $("#dataFim").val();

        window.location.href = "/relatorios/" + tipoRelatorio + "/excel?data_inicio=" + dataInicio + "&data_fim=" + dataFim;
    });
});
</script>
';

include(resource_path('views/layout.php'));
