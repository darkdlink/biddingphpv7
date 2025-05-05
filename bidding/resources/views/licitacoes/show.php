<?php
$content = '
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalhes da Licitação</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="btnVoltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <button type="button" class="btn btn-sm btn-primary" id="btnNovaProposta">
            <i class="fas fa-file-signature"></i> Nova Proposta
        </button>
    </div>
</div>

<div class="alert alert-success" id="alertSuccess" style="display: none;">
    <span id="alertMessage"></span>
</div>

<div class="alert alert-danger" id="alertError" style="display: none;">
    <span id="errorMessage"></span>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Informações Básicas</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nº Controle PNCP:</strong> <?php echo htmlspecialchars($licitacao->numero_controle_pncp); ?></p>
                <p><strong>Órgão:</strong> <?php echo htmlspecialchars($licitacao->orgao_entidade); ?></p>
                <p><strong>Unidade:</strong> <?php echo htmlspecialchars($licitacao->unidade_orgao); ?></p>
                <p><strong>Modalidade:</strong> <?php echo htmlspecialchars($licitacao->modalidade_nome); ?></p>
                <p><strong>Modo de Disputa:</strong> <?php echo htmlspecialchars($licitacao->modo_disputa_nome); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Número:</strong> <?php echo htmlspecialchars($licitacao->numero_compra); ?></p>
                <p><strong>Valor Estimado:</strong> R$ <?php echo number_format($licitacao->valor_total_estimado, 2, ",", "."); ?></p>
                <p><strong>Data de Publicação:</strong> <?php echo $licitacao->data_publicacao_pncp->format("d/m/Y"); ?></p>
                <p><strong>Data de Abertura:</strong> <?php echo $licitacao->data_abertura_proposta->format("d/m/Y H:i"); ?></p>
                <p><strong>Data de Encerramento:</strong> <?php echo $licitacao->data_encerramento_proposta->format("d/m/Y H:i"); ?></p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <p><strong>Objeto:</strong> <?php echo htmlspecialchars($licitacao->objeto_compra); ?></p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <p>
                    <strong>Link Sistema Origem:</strong>
                    <a href="<?php echo htmlspecialchars($licitacao->link_sistema_origem); ?>" target="_blank">
                        Acessar <i class="fas fa-external-link-alt"></i>
                    </a>
                </p>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="toggleInteresse"
                           <?php echo $licitacao->interesse ? "checked" : ""; ?>>
                    <label class="form-check-label" for="toggleInteresse">Marcar como Interesse</label>
                </div>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" id="toggleAnalisada"
                           <?php echo $licitacao->analisada ? "checked" : ""; ?>>
                    <label class="form-check-label" for="toggleAnalisada">Marcar como Analisada</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Propostas</h5>
    </div>
    <div class="card-body">
        <?php if (count($licitacao->propostas) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data de Envio</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licitacao->propostas as $proposta): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($proposta->cliente->nome); ?></td>
                        <td>R$ <?php echo number_format($proposta->valor_proposta, 2, ",", "."); ?></td>
                        <td>
                            <span class="badge <?php echo $this->getStatusBadgeClass($proposta->status); ?>">
                                <?php echo $this->getStatusLabel($proposta->status); ?>
                            </span>
                        </td>
                        <td><?php echo $proposta->data_envio ? $proposta->data_envio->format("d/m/Y H:i") : "Não enviada"; ?></td>
                        <td>
                            <a href="/propostas/<?php echo $proposta->id; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/propostas/<?php echo $proposta->id; ?>/edit" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            Nenhuma proposta cadastrada para esta licitação.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Nova Proposta -->
<div class="modal fade" id="propostaModal" tabindex="-1" aria-labelledby="propostaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propostaModalLabel">Nova Proposta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formProposta" action="/propostas" method="POST">
                    <input type="hidden" name="licitacao_id" value="<?php echo $licitacao->id; ?>">

                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <!-- Os clientes serão carregados via AJAX -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="valor_proposta" class="form-label">Valor da Proposta</label>
                        <input type="number" class="form-control" id="valor_proposta" name="valor_proposta" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarProposta">Salvar Proposta</button>
            </div>
        </div>
    </div>
</div>
';

// Helper functions
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
    // Voltar para a listagem
    $("#btnVoltar").click(function() {
        window.location.href = "/licitacoes";
    });

    // Manipular alteração no toggle de interesse
    $("#toggleInteresse").change(function() {
        var interesse = $(this).prop("checked") ? 1 : 0;

        $.ajax({
            url: "/licitacoes/<?php echo $licitacao->id; ?>/interesse",
            type: "POST",
            data: {
                interesse: interesse,
                _token: "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#alertSuccess").show();
                    $("#alertMessage").text(response.message);

                    setTimeout(function() {
                        $("#alertSuccess").hide();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                $("#alertError").show();
                $("#errorMessage").text("Erro ao atualizar interesse: " + error);

                setTimeout(function() {
                    $("#alertError").hide();
                }, 3000);
            }
        });
    });

    // Manipular alteração no toggle de analisada
    $("#toggleAnalisada").change(function() {
        var analisada = $(this).prop("checked") ? 1 : 0;

        $.ajax({
            url: "/licitacoes/<?php echo $licitacao->id; ?>/analisada",
            type: "POST",
            data: {
                analisada: analisada,
                _token: "{{ csrf_token() }}"
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $("#alertSuccess").show();
                    $("#alertMessage").text(response.message);

                    setTimeout(function() {
                        $("#alertSuccess").hide();
                    }, 3000);
                }
            },
            error: function(xhr, status, error) {
                $("#alertError").show();
                $("#errorMessage").text("Erro ao atualizar status de análise: " + error);

                setTimeout(function() {
                    $("#alertError").hide();
                }, 3000);
            }
        });
    });

    // Abrir modal de nova proposta
    $("#btnNovaProposta").click(function() {
        // Carregar clientes via AJAX
        $.ajax({
            url: "/clientes/lista",
            type: "GET",
            dataType: "json",
            success: function(response) {
                var options = "<option value=\'\'>Selecione um cliente</option>";

                $.each(response, function(index, cliente) {
                    options += "<option value=\'" + cliente.id + "\'>" + cliente.nome + "</option>";
                });

                $("#cliente_id").html(options);
            }
        });

        $("#propostaModal").modal("show");
    });

    // Salvar proposta
    $("#btnSalvarProposta").click(function() {
        if ($("#formProposta")[0].checkValidity()) {
            $.ajax({
                url: "/propostas",
                type: "POST",
                data: $("#formProposta").serialize() + "&_token={{ csrf_token() }}",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        $("#propostaModal").modal("hide");

                        $("#alertSuccess").show();
                        $("#alertMessage").text(response.message);

                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    $("#alertError").show();
                    $("#errorMessage").text("Erro ao salvar proposta: " + error);
                }
            });
        } else {
            $("#formProposta")[0].reportValidity();
        }
    });
});
</script>
';

include(resource_path('views/layout.php'));
