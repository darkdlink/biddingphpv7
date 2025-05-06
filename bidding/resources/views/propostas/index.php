<?php
// Inicia o buffer de saída para a variável $content
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Propostas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/propostas/create" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-plus"></i> Nova Proposta
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filtrosModal">
            <i class="fas fa-filter"></i> Filtros
        </button>
    </div>
</div>

<!-- Mensagens de alerta (já existentes, serão usadas pelo AJAX também) -->
<?php if (session()->has("success")): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert" id="alertSuccessGlobal">
    <?php echo htmlspecialchars(session("success")); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php endif; ?>

<?php if (session()->has("error")): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertErrorGlobal">
    <?php echo htmlspecialchars(session("error")); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php endif; ?>

<!-- Alertas AJAX específicos (se precisar de posicionamento diferente ou não quiser reusar os de sessão) -->
<div class="alert alert-success alert-dismissible fade show" role="alert" id="alertSuccessAjax" style="display: none;">
    <span id="alertMessageAjax"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertErrorAjax" style="display: none;">
    <span id="errorMessageAjax"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>


<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Licitação</th>
                <th>Cliente</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Data de Envio</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($propostas) && (is_array($propostas) || is_object($propostas)) && count($propostas) > 0): ?>
                <?php foreach ($propostas as $proposta): ?>
                <tr>
                    <td><?php echo htmlspecialchars($proposta->id ?? 'N/A'); ?></td>
                    <td>
                        <?php if(isset($proposta->licitacao)): ?>
                            <?php echo htmlspecialchars($proposta->licitacao->numero_compra ?? "N/A"); ?> -
                            <?php
                                $orgaoEntidade = $proposta->licitacao->orgao_entidade ?? "N/A";
                                echo htmlspecialchars(substr($orgaoEntidade, 0, 30)) . (strlen($orgaoEntidade) > 30 ? "..." : "");
                            ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($proposta->cliente->nome ?? "N/A"); ?></td>
                    <td>
                        <?php
                        if(isset($proposta->valor_proposta) && is_numeric($proposta->valor_proposta)) {
                            echo "R$ " . htmlspecialchars(number_format(floatval($proposta->valor_proposta), 2, ",", "."));
                        } else {
                            echo "N/A";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $status = $proposta->status ?? "N/A";
                        $badgeClass = "bg-secondary";
                        $statusLabel = ucfirst($status); // Valor padrão

                        if($status == "elaboracao") {
                            $badgeClass = "bg-secondary";
                            $statusLabel = "Em Elaboração";
                        } elseif($status == "enviada") {
                            $badgeClass = "bg-primary";
                            $statusLabel = "Enviada";
                        } elseif($status == "aceita") {
                            $badgeClass = "bg-success";
                            $statusLabel = "Aceita";
                        } elseif($status == "rejeitada") {
                            $badgeClass = "bg-danger";
                            $statusLabel = "Rejeitada";
                        } elseif($status == "vencedora") {
                            $badgeClass = "bg-warning text-dark";
                            $statusLabel = "Vencedora";
                        }
                        ?>
                        <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                            <?php echo htmlspecialchars($statusLabel); ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        if(isset($proposta->data_envio)) {
                            if(is_string($proposta->data_envio)) {
                                // Tentar converter se for uma string de data válida, senão exibir como está
                                try {
                                    $date = new DateTime($proposta->data_envio);
                                    echo htmlspecialchars($date->format("d/m/Y H:i"));
                                } catch (Exception $e) {
                                    echo htmlspecialchars($proposta->data_envio); // Exibe a string original se não for data válida
                                }
                            } elseif ($proposta->data_envio instanceof \DateTimeInterface) {
                                echo htmlspecialchars($proposta->data_envio->format("d/m/Y H:i"));
                            } else {
                                echo "Data inválida";
                            }
                        } else {
                            echo "Não enviada";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="/propostas/<?php echo htmlspecialchars($proposta->id ?? ''); ?>" class="btn btn-sm btn-info" title="Ver Detalhes">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php // Só permite editar se estiver em elaboração ?>
                        <?php if(isset($proposta->status) && $proposta->status == "elaboracao"): ?>
                        <a href="/propostas/<?php echo htmlspecialchars($proposta->id ?? ''); ?>/edit" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-success enviar-proposta" data-id="<?php echo htmlspecialchars($proposta->id ?? ''); ?>" title="Enviar Proposta">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">
                        Nenhuma proposta encontrada.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if(isset($propostas) && is_object($propostas) && method_exists($propostas, "links") && method_exists($propostas, "currentPage") && method_exists($propostas, "lastPage")): ?>
<nav aria-label="Paginação">
    <ul class="pagination justify-content-center">
        <?php
        $currentPage = $propostas->currentPage();
        $lastPage = $propostas->lastPage();
        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;
        $queryString = http_build_query(request()->except('page')); // Mantém outros filtros
        ?>

        <li class="page-item <?php echo $prevPage < 1 ? "disabled" : ""; ?>">
            <a class="page-link" href="?page=<?php echo $prevPage; ?><?php echo $queryString ? '&'.$queryString : ''; ?>" tabindex="-1">Anterior</a>
        </li>

        <?php for ($i = 1; $i <= $lastPage; $i++): ?>
        <li class="page-item <?php echo $i == $currentPage ? "active" : ""; ?>">
            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $queryString ? '&'.$queryString : ''; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <li class="page-item <?php echo $nextPage > $lastPage ? "disabled" : ""; ?>">
            <a class="page-link" href="?page=<?php echo $nextPage; ?><?php echo $queryString ? '&'.$queryString : ''; ?>">Próximo</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<!-- Modal de Filtros -->
<div class="modal fade" id="filtrosModal" tabindex="-1" aria-labelledby="filtrosModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filtrosModalLabel">Filtrar Propostas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formFiltros" action="/propostas" method="GET">
                    <div class="mb-3">
                        <label for="filtro_cliente_id" class="form-label">Cliente</label>
                        <select class="form-select" id="filtro_cliente_id" name="cliente_id">
                            <option value="">Todos</option>
                            <?php if(isset($clientes) && (is_array($clientes) || is_object($clientes))): ?>
                                <?php foreach($clientes as $cliente): ?>
                                <option value="<?php echo htmlspecialchars($cliente->id); ?>" <?php echo (isset($filtros["cliente_id"]) && $filtros["cliente_id"] == $cliente->id) ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($cliente->nome); ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="filtro_status" class="form-label">Status</label>
                        <select class="form-select" id="filtro_status" name="status">
                            <option value="">Todos</option>
                            <option value="elaboracao" <?php echo (isset($filtros["status"]) && $filtros["status"] == "elaboracao") ? "selected" : ""; ?>>Em Elaboração</option>
                            <option value="enviada" <?php echo (isset($filtros["status"]) && $filtros["status"] == "enviada") ? "selected" : ""; ?>>Enviada</option>
                            <option value="aceita" <?php echo (isset($filtros["status"]) && $filtros["status"] == "aceita") ? "selected" : ""; ?>>Aceita</option>
                            <option value="rejeitada" <?php echo (isset($filtros["status"]) && $filtros["status"] == "rejeitada") ? "selected" : ""; ?>>Rejeitada</option>
                            <option value="vencedora" <?php echo (isset($filtros["status"]) && $filtros["status"] == "vencedora") ? "selected" : ""; ?>>Vencedora</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="filtro_data_min" class="form-label">Data Min. Envio</label>
                            <input type="date" class="form-control" id="filtro_data_min" name="data_min" value="<?php echo htmlspecialchars($filtros["data_min"] ?? ""); ?>">
                        </div>
                        <div class="col">
                            <label for="filtro_data_max" class="form-label">Data Máx. Envio</label>
                            <input type="date" class="form-control" id="filtro_data_max" name="data_max" value="<?php echo htmlspecialchars($filtros["data_max"] ?? ""); ?>">
                        </div>
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

<!-- Modal de Confirmação de Envio -->
<div class="modal fade" id="enviarPropostaModal" tabindex="-1" aria-labelledby="enviarPropostaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enviarPropostaModalLabel">Confirmar Envio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja enviar esta proposta? Esta ação não pode ser desfeita.</p>
                <p>Após o envio, a proposta não poderá mais ser editada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarEnvio">Confirmar Envio</button>
            </div>
        </div>
    </div>
</div>

<?php
// Captura o conteúdo do buffer para a variável $content e limpa o buffer.
$content = ob_get_clean();

// Inicia o buffer de saída para a variável $scripts
ob_start();
?>
<script>
$(document).ready(function() {
    // Função para exibir alertas AJAX
    function showAlertAjax(type, message) {
        var alertId = type === 'success' ? '#alertSuccessAjax' : '#alertErrorAjax';
        var messageId = type === 'success' ? '#alertMessageAjax' : '#errorMessageAjax';

        $(messageId).text(message);
        $(alertId).fadeTo(2000, 500).slideUp(500, function() {
            $(alertId).slideUp(500);
        });
    }


    // Aplicar filtros
    $("#btnAplicarFiltros").click(function() {
        $("#formFiltros").submit();
    });

    // Manipular envio de proposta
    $(".enviar-proposta").click(function() {
        var propostaId = $(this).data("id");
        $("#btnConfirmarEnvio").data("id", propostaId); // Armazena ID no botão de confirmação
        var enviarModal = new bootstrap.Modal(document.getElementById('enviarPropostaModal'));
        enviarModal.show();
    });

    // Confirmar envio de proposta
    $("#btnConfirmarEnvio").click(function() {
        var propostaId = $(this).data("id");
        var csrfToken = $("meta[name=csrf-token]").attr("content"); // Pega o token CSRF

        // Fechar modal de confirmação
        var enviarModal = bootstrap.Modal.getInstance(document.getElementById('enviarPropostaModal'));
        if (enviarModal) {
            enviarModal.hide();
        }

        $.ajax({
            url: "/propostas/" + propostaId + "/enviar",
            type: "POST",
            data: {
                _token: csrfToken // Envia o token CSRF
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    // Usar o alerta global se ele existir, ou o alerta AJAX específico
                    if ($('#alertSuccessGlobal').length) {
                        $('#alertSuccessGlobal').html(response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>').show();
                    } else {
                        showAlertAjax('success', response.message || "Proposta enviada com sucesso!");
                    }
                    // Recarregar a página após um breve delay para o usuário ver a mensagem
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    if ($('#alertErrorGlobal').length) {
                         $('#alertErrorGlobal').html((response.message || "Erro ao enviar proposta.") + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>').show();
                    } else {
                        showAlertAjax('danger', response.message || "Erro ao enviar proposta.");
                    }
                }
            },
            error: function(xhr, status, error) {
                 if ($('#alertErrorGlobal').length) {
                    $('#alertErrorGlobal').html("Erro ao enviar proposta: " + error + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>').show();
                 } else {
                    showAlertAjax('danger', "Erro ao enviar proposta: " + error);
                 }
            }
        });
    });

    // Fecha alertas globais após alguns segundos (opcional)
    setTimeout(function() {
        $("#alertSuccessGlobal, #alertErrorGlobal").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 5000);
});
</script>
<?php
// Captura o conteúdo do buffer para a variável $scripts e limpa o buffer.
$scripts = ob_get_clean();

include(resource_path("views/layout.php"));
?>
