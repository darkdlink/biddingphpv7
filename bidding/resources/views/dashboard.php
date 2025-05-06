<?php
// Inicia o buffer de saída para a variável $content
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSincronizar">
                <i class="fas fa-sync"></i> Sincronizar Licitações
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnRelatorios">
            <i class="fas fa-file-alt"></i> Relatórios
        </button>
    </div>
</div>

<div class="alert alert-success" id="alertSuccess" style="display: none;">
    <span id="alertMessage"></span>
</div>

<div class="alert alert-danger" id="alertError" style="display: none;">
    <span id="errorMessage"></span>
</div>

<!-- Cards informativos -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total de Licitações</h5>
                <h2 class="card-text"><?php echo htmlspecialchars($totalLicitacoes ?? 0); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Licitações em Andamento</h5>
                <h2 class="card-text"><?php echo htmlspecialchars($licitacoesEmAndamento ?? 0); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Licitações com Interesse</h5>
                <h2 class="card-text"><?php echo htmlspecialchars($licitacoesInteresse ?? 0); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total de Propostas</h5>
                <h2 class="card-text"><?php echo htmlspecialchars($totalPropostas ?? 0); ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Gráfico de propostas por status -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Propostas por Status</h5>
            </div>
            <div class="card-body">
                <canvas id="chartPropostas" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de licitações por UF -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Licitações por UF (Top 10)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartLicitacoes" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Próximas licitações que encerram -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Próximas Licitações que Encerram (Com Interesse)</h5>
            </div>
            <div class="card-body">
                <?php if (isset($proximasLicitacoes) && count($proximasLicitacoes) > 0): ?>
                <div class="list-group">
                    <?php foreach ($proximasLicitacoes as $licitacao): ?>
                    <a href="/licitacoes/<?php echo htmlspecialchars($licitacao->id ?? ''); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($licitacao->orgao_entidade ?? 'N/A'); ?></h6>
                            <small class="text-danger">
                                <?php
                                if (isset($licitacao->data_encerramento_proposta) && $licitacao->data_encerramento_proposta instanceof \DateTimeInterface) {
                                    echo htmlspecialchars($licitacao->data_encerramento_proposta->format("d/m/Y H:i"));
                                } else {
                                    echo 'Data N/A';
                                }
                                ?>
                            </small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars(substr($licitacao->objeto_compra ?? '', 0, 100)) . (strlen($licitacao->objeto_compra ?? '') > 100 ? "..." : ""); ?></p>
                        <small>Valor: R$ <?php echo htmlspecialchars(number_format(floatval($licitacao->valor_total_estimado ?? 0), 2, ",", ".")); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Nenhuma licitação com interesse encerra nos próximos 7 dias.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Últimas propostas enviadas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Últimas Propostas Enviadas</h5>
            </div>
            <div class="card-body">
                <?php if (isset($ultimasPropostas) && count($ultimasPropostas) > 0): ?>
                <div class="list-group">
                    <?php foreach ($ultimasPropostas as $proposta): ?>
                    <a href="/propostas/<?php echo htmlspecialchars($proposta->id ?? ''); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($proposta->cliente->nome ?? 'Cliente N/A'); ?></h6>
                            <small class="text-muted">
                                <?php
                                if (isset($proposta->data_envio) && $proposta->data_envio instanceof \DateTimeInterface) {
                                    echo htmlspecialchars($proposta->data_envio->format("d/m/Y H:i"));
                                } else {
                                    echo 'Data N/A';
                                }
                                ?>
                            </small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars(substr($proposta->licitacao->objeto_compra ?? '', 0, 100)) . (strlen($proposta->licitacao->objeto_compra ?? '') > 100 ? "..." : ""); ?></p>
                        <small>
                            Valor: R$ <?php echo htmlspecialchars(number_format(floatval($proposta->valor_proposta ?? 0), 2, ",", ".")); ?> |
                            Status: <span class="badge <?php echo getStatusBadgeClass($proposta->status ?? ''); ?>">
                                <?php echo getStatusLabel($proposta->status ?? ''); ?>
                            </span>
                        </small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Nenhuma proposta enviada até o momento.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Captura o conteúdo do buffer para a variável $content e limpa o buffer.
$content = ob_get_clean();

// Helper functions (devem estar definidas antes de $content = ob_get_clean() se fossem usadas dentro do buffer,
// mas como estão fora, e $scripts as usa como texto, elas podem ficar aqui ou no topo do arquivo)
if (!function_exists('getStatusBadgeClass')) { // Evita redeclaração se incluído múltiplas vezes
    function getStatusBadgeClass($status) {
        switch ($status) {
            case 'elaboracao': return 'bg-secondary';
            case 'enviada': return 'bg-primary';
            case 'aceita': return 'bg-success';
            case 'rejeitada': return 'bg-danger';
            case 'vencedora': return 'bg-warning text-dark';
            default: return 'bg-light text-dark'; // Alterado para ser mais visível
        }
    }
}

if (!function_exists('getStatusLabel')) { // Evita redeclaração
    function getStatusLabel($status) {
        switch ($status) {
            case 'elaboracao': return 'Em Elaboração';
            case 'enviada': return 'Enviada';
            case 'aceita': return 'Aceita';
            case 'rejeitada': return 'Rejeitada';
            case 'vencedora': return 'Vencedora';
            default: return 'Desconhecido';
        }
    }
}


// Inicia o buffer de saída para a variável $scripts
ob_start();
?>
<script>
$(document).ready(function() {
    // Gráfico de propostas por status
    var ctxPropostas = document.getElementById("chartPropostas")?.getContext("2d");
    if (ctxPropostas) {
        var chartPropostas = new Chart(ctxPropostas, {
            type: "pie",
            data: {
                labels: ["Em Elaboração", "Enviada", "Aceita", "Rejeitada", "Vencedora"],
                datasets: [{
                    data: [
                        <?php echo intval($propostasPorStatus["elaboracao"] ?? 0); ?>,
                        <?php echo intval($propostasPorStatus["enviada"] ?? 0); ?>,
                        <?php echo intval($propostasPorStatus["aceita"] ?? 0); ?>,
                        <?php echo intval($propostasPorStatus["rejeitada"] ?? 0); ?>,
                        <?php echo intval($propostasPorStatus["vencedora"] ?? 0); ?>
                    ],
                    backgroundColor: [
                        "#6c757d", // Cinza para Elaboração
                        "#007bff", // Azul para Enviada
                        "#28a745", // Verde para Aceita
                        "#dc3545", // Vermelho para Rejeitada
                        "#ffc107"  // Amarelo para Vencedora
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de licitações por UF
    var ctxLicitacoes = document.getElementById("chartLicitacoes")?.getContext("2d");
    if (ctxLicitacoes) {
        var chartLicitacoes = new Chart(ctxLicitacoes, {
            type: "bar",
            data: {
                labels: [
                    <?php
                    if (isset($licitacoesPorUF) && is_array($licitacoesPorUF)) {
                        foreach ($licitacoesPorUF as $licitacaoUF) {
                            echo "'" . htmlspecialchars($licitacaoUF->uf ?? 'N/A') . "',";
                        }
                    }
                    ?>
                ],
                datasets: [{
                    label: "Quantidade",
                    data: [
                        <?php
                        if (isset($licitacoesPorUF) && is_array($licitacoesPorUF)) {
                            foreach ($licitacoesPorUF as $licitacaoUF) {
                                echo intval($licitacaoUF->total ?? 0) . ",";
                            }
                        }
                        ?>
                    ],
                    backgroundColor: "rgba(0, 123, 255, 0.6)",
                    borderColor: "rgba(0, 123, 255, 1)",
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Geralmente não necessário para gráficos de barra com um único dataset
                    }
                }
            }
        });
    }

    // Manipular clique no botão Sincronizar
    $("#btnSincronizar").click(function() {
        var $this = $(this);
        $this.prop("disabled", true);
        $this.html("<i class='fas fa-spinner fa-spin'></i> Sincronizando...");

        $.ajax({
            url: "/licitacoes/sincronizar",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#alertSuccess").show();
                    $("#alertMessage").text(response.message + " Total: " + (response.total_registros || 0) + " registros.");
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $("#alertError").show();
                    $("#errorMessage").text(response.message || "Ocorreu um erro na sincronização.");
                    $this.prop("disabled", false);
                    $this.html("<i class='fas fa-sync'></i> Sincronizar Licitações");
                }
            },
            error: function(xhr, status, error) {
                $("#alertError").show();
                $("#errorMessage").text("Erro ao sincronizar licitações: " + error);
                $this.prop("disabled", false);
                $this.html("<i class='fas fa-sync'></i> Sincronizar Licitações");
            }
            // O complete foi removido para evitar reabilitar o botão se o success já for recarregar a página.
        });
    });

    // Redirecionamento para relatórios
    $("#btnRelatorios").click(function() {
        window.location.href = "/relatorios";
    });
});
</script>
<?php
// Captura o conteúdo do buffer para a variável $scripts e limpa o buffer.
$scripts = ob_get_clean();

include(resource_path('views/layout.php'));
?>
