<?php
$content = '
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Licitações</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="btnSincronizar">
            <i class="fas fa-sync"></i> Sincronizar Licitações
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filtrosModal">
            <i class="fas fa-filter"></i> Filtros
        </button>
    </div>
</div>

<div class="alert alert-success" id="alertSuccess" style="display: none;">
    <span id="alertMessage"></span>
</div>

<div class="alert alert-danger" id="alertError" style="display: none;">
    <span id="errorMessage"></span>
</div>

<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Nº Controle</th>
                <th>Órgão</th>
                <th>Objeto</th>
                <th>Modalidade</th>
                <th>Valor Estimado</th>
                <th>Data Encerramento</th>
                <th>UF</th>
                <th>Situação</th>
                <th>Interesse</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($licitacoes) && count($licitacoes) > 0): ?>
                <?php foreach ($licitacoes as $licitacao): ?>
                <tr>
                    <td><?php echo htmlspecialchars($licitacao->numero_controle_pncp ?? "N/A"); ?></td>
                    <td><?php echo htmlspecialchars($licitacao->orgao_entidade ?? "N/A"); ?></td>
                    <td>
                        <?php
                        $objeto = $licitacao->objeto_compra ?? "N/A";
                        echo htmlspecialchars(strlen($objeto) > 100 ? substr($objeto, 0, 97) . "..." : $objeto);
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($licitacao->modalidade_nome ?? "N/A"); ?></td>
                    <td>
                        <?php
                        if(isset($licitacao->valor_total_estimado) && is_numeric($licitacao->valor_total_estimado)) {
                            echo "R$ " . number_format($licitacao->valor_total_estimado, 2, ",", ".");
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if(isset($licitacao->data_encerramento_proposta)) {
                            try {
                                echo $licitacao->data_encerramento_proposta->format("d/m/Y H:i");
                            } catch (Exception $e) {
                                echo "Data inválida";
                            }
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($licitacao->uf ?? "N/A"); ?></td>
                    <td>
                        <?php
                        $situacao = $licitacao->situacao_compra_nome ?? "N/A";
                        $badgeClass = "bg-secondary";
                        if($situacao == "Em andamento" || $situacao == "Divulgada no PNCP") {
                            $badgeClass = "bg-success";
                        }
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>">
                            <?php echo htmlspecialchars($situacao); ?>
                        </span>
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-interesse" type="checkbox"
                                   data-id="<?php echo $licitacao->id; ?>"
                                   <?php echo ($licitacao->interesse ?? false) ? "checked" : ""; ?>>
                        </div>
                    </td>
                    <td>
                        <a href="/licitacoes/<?php echo $licitacao->id; ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-sm btn-primary criar-proposta" data-id="<?php echo $licitacao->id; ?>">
                            <i class="fas fa-file-signature"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">Nenhuma licitação encontrada. Clique em "Sincronizar Licitações" para buscar dados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if(isset($licitacoes) && method_exists($licitacoes, "links")): ?>
<nav aria-label="Paginação">
    <ul class="pagination justify-content-center">
        <?php
        $prevPage = $licitacoes->currentPage() - 1;
        $nextPage = $licitacoes->currentPage() + 1;
        ?>

        <li class="page-item <?php echo $prevPage < 1 ? "disabled" : ""; ?>">
            <a class="page-link" href="?page=<?php echo $prevPage; ?>" tabindex="-1">Anterior</a>
        </li>

        <?php for ($i = 1; $i <= $licitacoes->lastPage(); $i++): ?>
        <li class="page-item <?php echo $i == $licitacoes->currentPage() ? "active" : ""; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <li class="page-item <?php echo $nextPage > $licitacoes->lastPage() ? "disabled" : ""; ?>">
            <a class="page-link" href="?page=<?php echo $nextPage; ?>">Próximo</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal de Filtros -->
<div class="modal fade" id="filtrosModal" tabindex="-1" aria-labelledby="filtrosModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filtrosModalLabel">Filtrar Licitações</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltros" action="/licitacoes" method="GET">
                    <div class="mb-3">
                        <label for="uf" class="form-label">UF</label>
                        <select class="form-select" id="uf" name="uf">
                            <option value="">Todos</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC">Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modalidade" class="form-label">Modalidade</label>
                        <select class="form-select" id="modalidade" name="modalidade">
                            <option value="">Todas</option>
                            <option value="Pregão">Pregão</option>
                            <option value="Concorrência">Concorrência</option>
                            <option value="Tomada de Preços">Tomada de Preços</option>
                            <option value="Convite">Convite</option>
                            <option value="Concurso">Concurso</option>
                            <option value="Leilão">Leilão</option>
                            <option value="Dispensa">Dispensa</option>
                            <option value="Inexigibilidade">Inexigibilidade</option>
                            <option value="Credenciamento">Credenciamento</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="data_min" class="form-label">Data Min. Encerramento</label>
                            <input type="date" class="form-control" id="data_min" name="data_min">
                        </div>
                        <div class="col">
                            <label for="data_max" class="form-label">Data Máx. Encerramento</label>
                            <input type="date" class="form-control" id="data_max" name="data_max">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="valor_min" class="form-label">Valor Mínimo</label>
                            <input type="number" class="form-control" id="valor_min" name="valor_min" step="0.01">
                        </div>
                        <div class="col">
                            <label for="valor_max" class="form-label">Valor Máximo</label>
                            <input type="number" class="form-control" id="valor_max" name="valor_max" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="interesse" class="form-label">Interesse</label>
                        <select class="form-select" id="interesse" name="interesse">
                            <option value="">Todos</option>
                            <option value="1">Com Interesse</option>
                            <option value="0">Sem Interesse</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnAplicarFiltros">Aplicar Filtros</button>
            </div>
        </div>
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
                    <input type="hidden" id="licitacao_id" name="licitacao_id">

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

$scripts = '
<script>
$(document).ready(function() {
    // Manipular clique no botão Sincronizar
    $("#btnSincronizar").click(function() {
        $(this).prop("disabled", true);
        $(this).html("<i class=\'fas fa-spinner fa-spin\'></i> Sincronizando...");

        // Mostrar uma mensagem de carregamento
        $("#alertSuccess").show();
        $("#alertMessage").text("Iniciando sincronização, por favor aguarde...");

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
            },
            timeout: 120000 // 2 minutos de timeout
        });
    });

    // Manipular alteração no toggle de interesse
    $(".toggle-interesse").change(function() {
        var id = $(this).data("id");
        var interesse = $(this).prop("checked") ? 1 : 0;

        $.ajax({
            url: "/licitacoes/" + id + "/interesse",
            type: "POST",
            data: {
                interesse: interesse,
                _token: $("meta[name=csrf-token]").attr("content")
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

    // Aplicar filtros
    $("#btnAplicarFiltros").click(function() {
        $("#formFiltros").submit();
    });

    // Abrir modal de nova proposta
    $(".criar-proposta").click(function() {
        var licitacaoId = $(this).data("id");
        $("#licitacao_id").val(licitacaoId);

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
            var formData = $("#formProposta").serialize();
            formData += "&_token=" + $("meta[name=csrf-token]").attr("content");

            $.ajax({
                url: "/propostas",
                type: "POST",
                data: formData,
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
