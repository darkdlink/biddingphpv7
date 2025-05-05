<?php
$content = '
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
                <h2 class="card-text"><?php echo $totalLicitacoes; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Licitações em Andamento</h5>
                <h2 class="card-text"><?php echo $licitacoesEmAndamento; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Licitações com Interesse</h5>
                <h2 class="card-text"><?php echo $licitacoesInteresse; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total de Propostas</h5>
                <h2 class="card-text"><?php echo $totalPropostas; ?></h2>
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
                <?php if (count($proximasLicitacoes) > 0): ?>
                <div class="list-group">
                    <?php foreach ($proximasLicitacoes as $licitacao): ?>
                    <a href="/licitacoes/<?php echo $licitacao->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($licitacao->orgao_entidade); ?></h6>
                            <small class="text-danger">
                                <?php echo $licitacao->data_encerramento_proposta->format("d/m/Y H:i"); ?>
                            </small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars(substr($licitacao->objeto_compra, 0, 100)) . "..."; ?></p>
                        <small>Valor: R$ <?php echo number_format($licitacao->valor_total_estimado, 2, ",", "."); ?></small>
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
                <?php if (count($ultimasPropostas) > 0): ?>
                <div class="list-group">
                    <?php foreach ($ultimasPropostas as $proposta): ?>
                    <a href="/propostas/<?php echo $proposta->id; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?php echo htmlspecialchars($proposta->cliente->nome); ?></h6>
                            <small class="text-muted">
                                <?php echo $proposta->data_envio->format("d/m/Y H:i"); ?>
                            </small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars(substr($proposta->licitacao->objeto_compra, 0, 100)) . "..."; ?></p>
                        <small>
                            Valor: R$ <?php echo number_format($proposta->valor_proposta, 2, ",", "."); ?> |
                            Status: <span class="badge <?php echo getStatusBadgeClass($proposta->status); ?>">
                                <?php echo getStatusLabel($proposta->status); ?>
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
';

// Helper functions (same as in the licitacoes/show.php view)
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'elaboracao':
            return 'bg-secondary';
        case 'enviada':
            return 'bg-primary';
        case 'aceita':
            return 'bg-success';
        case 'rejeitada':
            return 'bg-danger';
        case 'vencedora':
            return 'bg-warning text-dark';
        default:
            return 'bg-secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'elaboracao':
            return 'Em Elaboração';
        case 'enviada':
            return 'Enviada';
        case 'aceita':
            return 'Aceita';
        case 'rejeitada':
            return 'Rejeitada';
        case 'vencedora':
            return 'Vencedora';
        default:
            return 'Desconhecido';
    }
}

$scripts = '
<script>
$(document).ready(function() {
    // Gráfico de propostas por status
    var ctxPropostas = document.getElementById("chartPropostas").getContext("2d");
    var chartPropostas = new Chart(ctxPropostas, {
        type: "pie",
        data: {
            labels: ["Em Elaboração", "Enviada", "Aceita", "Rejeitada", "Vencedora"],
            datasets: [{
                data: [
                    <?php echo $propostasPorStatus["elaboracao"]; ?>,
                    <?php echo $propostasPorStatus["enviada"]; ?>,
                    <?php echo $propostasPorStatus["aceita"]; ?>,
                    <?php echo $propostasPorStatus["rejeitada"]; ?>,
                    <?php echo $propostasPorStatus["vencedora"]; ?>
                ],
                backgroundColor: [
                    "#6c757d",
                    "#007bff",
                    "#28a745",
                    "#dc3545",
                    "#ffc107"
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gráfico de licitações por UF
    var ctxLicitacoes = document.getElementById("chartLicitacoes").getContext("2d");
    var chartLicitacoes = new Chart(ctxLicitacoes, {
        type: "bar",
        data: {
            labels: [
                <?php foreach ($licitacoesPorUF as $licitacao): ?>
                "<?php echo $licitacao->uf; ?>",
                <?php endforeach; ?>
            ],
            datasets: [{
                label: "Quantidade",
                data: [
                    <?php foreach ($licitacoesPorUF as $licitacao): ?>
                    <?php echo $licitacao->total; ?>,
                    <?php endforeach; ?>
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
            }
        }
    });

    // Manipular clique no botão Sincronizar
    $("#btnSincronizar").click(function() {
        $(this).prop("disabled", true);
        $(this).html("<i class=\'fas fa-spinner fa-spin\'></i> Sincronizando...");

        // Chamada AJAX para sincronizar licitações
        $.ajax({
            url: "/licitacoes/sincronizar",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#alertSuccess").show();
                    $("#alertMessage").text(response.message + " Total: " + response.total_registros + " registros.");

                    // Recarregar a página após 2 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $("#alertError").show();
                    $("#errorMessage").text(response.message);
                }
            },
            error: function(xhr, status, error) {
                $("#alertError").show();
                $("#errorMessage").text("Erro ao sincronizar licitações: " + error);

                $("#btnSincronizar").prop("disabled", false);
                $("#btnSincronizar").html("<i class=\'fas fa-sync\'></i> Sincronizar Licitações");
            },
            complete: function() {
                $("#btnSincronizar").prop("disabled", false);
                $("#btnSincronizar").html("<i class=\'fas fa-sync\'></i> Sincronizar Licitações");
            }
        });
    });

    // Redirecionamento para relatórios
    $("#btnRelatorios").click(function() {
        window.location.href = "/relatorios";
    });
});
</script>
';

include(resource_path('views/layout.php'));
