<?php
// Inicia o buffer de saída para a variável $content
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciamento de Clientes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#novoClienteModal">
            <i class="fas fa-plus"></i> Novo Cliente
        </button>
    </div>
</div>

<!-- Alerta de sucesso/erro -->
<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo session('success'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo session('error'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>
<?php endif; ?>

<div class="alert alert-success alert-dismissible fade show" role="alert" id="alertSuccess" style="display: none;">
    <span id="successMessage"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>

<div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertError" style="display: none;">
    <span id="errorMessage"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>

<!-- Barra de busca -->
<form action="<?php echo route('clientes.search'); ?>" method="GET" class="mb-4">
    <div class="input-group">
        <input type="text" name="termo" class="form-control" placeholder="Buscar por nome, CNPJ ou email..." value="<?php echo isset($termo) ? htmlspecialchars($termo) : ''; ?>">
        <button class="btn btn-outline-secondary" type="submit">
            <i class="fas fa-search"></i> Buscar
        </button>
    </div>
</form>

<!-- Tabela de Clientes -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>CNPJ</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Cidade/UF</th>
                <th>Propostas</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($clientes) && (is_array($clientes) || is_object($clientes)) && count($clientes) > 0): ?>
                <?php foreach($clientes as $cliente): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cliente->id ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($cliente->nome ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($cliente->cnpj ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($cliente->email ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($cliente->telefone ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($cliente->cidade ?? 'N/A'); ?>/<?php echo htmlspecialchars($cliente->uf ?? 'N/A'); ?></td>
                    <td>
                        <?php echo htmlspecialchars($cliente->propostas_count ?? 0); ?>
                    </td>
                    <td>
                        <a href="<?php echo route('clientes.show', $cliente->id); ?>" class="btn btn-sm btn-info" title="Ver Detalhes">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="<?php echo route('clientes.edit', $cliente->id); ?>" class="btn btn-sm btn-warning" title="Editar Cliente">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger excluir-cliente" data-id="<?php echo htmlspecialchars($cliente->id ?? ''); ?>" title="Excluir Cliente">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Nenhum cliente cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if(isset($clientes) && is_object($clientes) && method_exists($clientes, "links")): ?>
    <?php echo $clientes->links(); ?>
<?php endif; ?>

<!-- Modal Novo Cliente -->
<div class="modal fade" id="novoClienteModal" tabindex="-1" aria-labelledby="novoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novoClienteModalLabel">Novo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoCliente" action="<?php echo route('clientes.store'); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="novo_nome" class="form-label">Nome/Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="novo_nome" name="nome" required>
                        </div>
                        <div class="col-md-4">
                            <label for="novo_cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="novo_cnpj" name="cnpj" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="novo_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="novo_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="novo_telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="novo_telefone" name="telefone" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="novo_endereco" class="form-label">Endereço <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="novo_endereco" name="endereco" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="novo_cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="novo_cidade" name="cidade" required>
                        </div>
                        <div class="col-md-3">
                            <label for="novo_uf" class="form-label">UF <span class="text-danger">*</span></label>
                            <select class="form-select" id="novo_uf" name="uf" required>
                                <option value="">Selecione</option>
                                <option value="AC">AC</option><option value="AL">AL</option><option value="AP">AP</option><option value="AM">AM</option>
                                <option value="BA">BA</option><option value="CE">CE</option><option value="DF">DF</option><option value="ES">ES</option>
                                <option value="GO">GO</option><option value="MA">MA</option><option value="MT">MT</option><option value="MS">MS</option>
                                <option value="MG">MG</option><option value="PA">PA</option><option value="PB">PB</option><option value="PR">PR</option>
                                <option value="PE">PE</option><option value="PI">PI</option><option value="RJ">RJ</option><option value="RN">RN</option>
                                <option value="RS">RS</option><option value="RO">RO</option><option value="RR">RR</option><option value="SC">SC</option>
                                <option value="SP">SP</option><option value="SE">SE</option><option value="TO">TO</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="novo_cep" class="form-label">CEP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="novo_cep" name="cep" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="novo_observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="novo_observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoCliente">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="editarClienteModal" tabindex="-1" aria-labelledby="editarClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarClienteModalLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarCliente">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <input type="hidden" id="editar_id_input" name="id">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="editar_nome" class="form-label">Nome/Razão Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_nome" name="nome" required>
                        </div>
                        <div class="col-md-4">
                            <label for="editar_cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_cnpj" name="cnpj" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editar_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="editar_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editar_telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_telefone" name="telefone" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_endereco" class="form-label">Endereço <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editar_endereco" name="endereco" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="editar_cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_cidade" name="cidade" required>
                        </div>
                        <div class="col-md-3">
                            <label for="editar_uf" class="form-label">UF <span class="text-danger">*</span></label>
                            <select class="form-select" id="editar_uf" name="uf" required>
                                <option value="">Selecione</option>
                                <option value="AC">AC</option><option value="AL">AL</option><option value="AP">AP</option><option value="AM">AM</option>
                                <option value="BA">BA</option><option value="CE">CE</option><option value="DF">DF</option><option value="ES">ES</option>
                                <option value="GO">GO</option><option value="MA">MA</option><option value="MT">MT</option><option value="MS">MS</option>
                                <option value="MG">MG</option><option value="PA">PA</option><option value="PB">PB</option><option value="PR">PR</option>
                                <option value="PE">PE</option><option value="PI">PI</option><option value="RJ">RJ</option><option value="RN">RN</option>
                                <option value="RS">RS</option><option value="RO">RO</option><option value="RR">RR</option><option value="SC">SC</option>
                                <option value="SP">SP</option><option value="SE">SE</option><option value="TO">TO</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="editar_cep" class="form-label">CEP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editar_cep" name="cep" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editar_observacoes" class="form-label">Observações</label>
                        <textarea class="form-control" id="editar_observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarEditarCliente">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Cliente -->
<div class="modal fade" id="verClienteModal" tabindex="-1" aria-labelledby="verClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verClienteModalLabel">Detalhes do Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-3">Nome/Razão Social:</dt>
                    <dd class="col-sm-9" id="ver_nome_text"></dd>

                    <dt class="col-sm-3">CNPJ:</dt>
                    <dd class="col-sm-9" id="ver_cnpj_text"></dd>

                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9" id="ver_email_text"></dd>

                    <dt class="col-sm-3">Telefone:</dt>
                    <dd class="col-sm-9" id="ver_telefone_text"></dd>

                    <dt class="col-sm-3">Endereço:</dt>
                    <dd class="col-sm-9" id="ver_endereco_text"></dd>

                    <dt class="col-sm-3">Cidade/UF:</dt>
                    <dd class="col-sm-9"><span id="ver_cidade_text"></span>/<span id="ver_uf_text"></span></dd>

                    <dt class="col-sm-3">CEP:</dt>
                    <dd class="col-sm-9" id="ver_cep_text"></dd>

                    <dt class="col-sm-3">Observações:</dt>
                    <dd class="col-sm-9" id="ver_observacoes_text"></dd>
                </dl>

                <h6 class="mt-4">Propostas (<span id="total_propostas_cliente">0</span>)</h6>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Licitação (Nº Compra)</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Data Envio</th>
                            </tr>
                        </thead>
                        <tbody id="tabela_propostas_cliente">
                            <!-- Será preenchido via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Cliente -->
<div class="modal fade" id="excluirClienteModal" tabindex="-1" aria-labelledby="excluirClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excluirClienteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.</p>
                <p><strong>Atenção:</strong> Se o cliente possuir propostas vinculadas, não será possível excluí-lo.</p>
                <input type="hidden" id="excluir_id_input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
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
    // Inicializar as máscaras para os campos
    $('#novo_cnpj, #editar_cnpj').mask('00.000.000/0000-00', {reverse: true});
    $('#novo_cep, #editar_cep').mask('00000-000');

    var SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    },
    spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };
    $('#novo_telefone, #editar_telefone').mask(SPMaskBehavior, spOptions);

    // Função para exibir alertas
    function showAlert(type, message, isHtml = false) {
        var alertId = type === 'success' ? '#alertSuccess' : '#alertError';
        var messageId = type === 'success' ? '#successMessage' : '#errorMessage';

        if (isHtml) {
            $(messageId).html(message);
        } else {
            $(messageId).text(message);
        }

        if (type === 'success') {
            $('#alertError').hide();
        } else {
            $('#alertSuccess').hide();
        }

        $(alertId).fadeIn();
        setTimeout(function() { $(alertId).fadeOut(); }, 5000);
    }

    // Resetar formulários ao fechar modais
    $('#novoClienteModal, #editarClienteModal').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    // Botão Salvar Novo Cliente
    $("#btnSalvarNovoCliente").click(function() {
        var form = $("#formNovoCliente");
        if (form[0].checkValidity() === false) {
            form[0].reportValidity();
            return;
        }

        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Salvando...");

        $.ajax({
            url: "<?php echo route('clientes.store'); ?>",
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#novoClienteModal').modal('hide');
                    showAlert('success', response.message || "Cliente cadastrado com sucesso!");
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    showAlert('danger', response.message || "Erro ao cadastrar cliente.");
                }
            },
            error: function(xhr) {
                var errors = "Erro ao cadastrar cliente.";
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errors = "";
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errors += value.join("<br>") + "<br>";
                    });
                    showAlert('danger', errors, true);
                } else if(xhr.responseJSON && xhr.responseJSON.message) {
                    showAlert('danger', xhr.responseJSON.message);
                } else {
                    showAlert('danger', errors);
                }
            },
            complete: function() {
                $this.prop("disabled", false).html("Salvar");
            }
        });
    });

    // Botão Editar Cliente
    $(document).on("click", ".editar-cliente", function() {
        var id = $(this).data("id");

        $.ajax({
            url: "<?php echo url('/clientes/'); ?>/" + id + "/edit",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success && response.cliente) {
                    var cliente = response.cliente;

                    $("#editar_id_input").val(cliente.id);
                    $("#editar_nome").val(cliente.nome);
                    $("#editar_cnpj").val(cliente.cnpj).trigger('input');
                    $("#editar_email").val(cliente.email);
                    $("#editar_telefone").val(cliente.telefone).trigger('input');
                    $("#editar_endereco").val(cliente.endereco);
                    $("#editar_cidade").val(cliente.cidade);
                    $("#editar_uf").val(cliente.uf);
                    $("#editar_cep").val(cliente.cep).trigger('input');
                    $("#editar_observacoes").val(cliente.observacoes);

                    $('#editarClienteModal').modal('show');
                } else {
                    showAlert('danger', response.message || "Cliente não encontrado para edição.");
                }
            },
            error: function() {
                showAlert('danger', "Erro ao carregar dados do cliente para edição.");
            }
        });
    });

    // Botão Salvar Editar Cliente
    $("#btnSalvarEditarCliente").click(function() {
        var form = $("#formEditarCliente");
        if (form[0].checkValidity() === false) {
            form[0].reportValidity();
            return;
        }

        var id = $("#editar_id_input").val();
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Salvando...");

        $.ajax({
            url: "<?php echo url('/clientes/'); ?>/" + id,
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#editarClienteModal').modal('hide');
                    showAlert('success', response.message || "Cliente atualizado com sucesso!");
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    showAlert('danger', response.message || "Erro ao atualizar cliente.");
                }
            },
            error: function(xhr) {
                var errors = "Erro ao atualizar cliente.";
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errors = "";
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errors += value.join("<br>") + "<br>";
                    });
                    showAlert('danger', errors, true);
                } else if(xhr.responseJSON && xhr.responseJSON.message) {
                    showAlert('danger', xhr.responseJSON.message);
                } else {
                    showAlert('danger', errors);
                }
            },
            complete: function() {
                $this.prop("disabled", false).html("Salvar Alterações");
            }
        });
    });

    // Botão Ver Cliente
    $(document).on("click", ".ver-cliente", function() {
        var id = $(this).data("id");
        $("#tabela_propostas_cliente").html("<tr><td colspan='5' class='text-center'><i class='fas fa-spinner fa-spin'></i> Carregando...</td></tr>");
        $("#total_propostas_cliente").text("0");

        $.ajax({
            url: "<?php echo url('/clientes/'); ?>/" + id,
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.success && response.cliente) {
                    var cliente = response.cliente;

                    $("#ver_nome_text").text(cliente.nome || 'N/A');
                    $("#ver_cnpj_text").text(cliente.cnpj || 'N/A');
                    $("#ver_email_text").text(cliente.email || 'N/A');
                    $("#ver_telefone_text").text(cliente.telefone || 'N/A');
                    $("#ver_endereco_text").text(cliente.endereco || 'N/A');
                    $("#ver_cidade_text").text(cliente.cidade || 'N/A');
                    $("#ver_uf_text").text(cliente.uf || 'N/A');
                    $("#ver_cep_text").text(cliente.cep || 'N/A');
                    $("#ver_observacoes_text").text(cliente.observacoes || '');

                    var propostas = response.propostas || [];
                    $("#total_propostas_cliente").text(propostas.length);

                    var htmlPropostas = "";
                    if (propostas.length > 0) {
                        $.each(propostas, function(i, proposta) {
                            var statusLabel = proposta.status_formatado || proposta.status || 'N/A';
                            var badgeClass = proposta.status_badge || "bg-secondary";
                            var dataEnvio = proposta.data_envio_formatada || (proposta.data_envio ? new Date(proposta.data_envio).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short'}) : "Não enviada");
                            var valorProposta = parseFloat(proposta.valor_proposta || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                            htmlPropostas += `<tr>
                                <td>${proposta.id || 'N/A'}</td>
                                <td>${proposta.licitacao_numero_compra || 'N/A'}</td>
                                <td>${valorProposta}</td>
                                <td><span class="badge ${badgeClass}">${statusLabel}</span></td>
                                <td>${dataEnvio}</td>
                            </tr>`;
                        });
                    } else {
                        htmlPropostas = "<tr><td colspan='5' class='text-center'>Nenhuma proposta cadastrada para este cliente.</td></tr>";
                    }

                    $("#tabela_propostas_cliente").html(htmlPropostas);
                    $('#verClienteModal').modal('show');
                } else {
                    showAlert('danger', response.message || "Cliente não encontrado.");
                }
            },
            error: function() {
                showAlert('danger', "Erro ao carregar dados do cliente.");
            }
        });
    });

// Botão Excluir Cliente (continuação)
$(document).on("click", ".excluir-cliente", function() {
        var id = $(this).data("id");
        $("#excluir_id_input").val(id);
        $('#excluirClienteModal').modal('show');
    });

    // Botão Confirmar Exclusão
    $("#btnConfirmarExclusao").click(function() {
        var id = $("#excluir_id_input").val();
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Excluindo...");

        $.ajax({
            url: "<?php echo url('/clientes/'); ?>/" + id,
            type: "POST",
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE"
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    $('#excluirClienteModal').modal('hide');
                    showAlert('success', response.message || "Cliente excluído com sucesso!");
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    showAlert('danger', response.message || "Erro ao excluir cliente.");
                }
            },
            error: function(xhr) {
                var errorMsg = "Erro ao excluir cliente.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert('danger', errorMsg);
            },
            complete: function() {
                $this.prop("disabled", false).html("Excluir");
            }
        });
    });

    // Fechar alertas
    $(document).on('click', '.alert .btn-close', function() {
        $(this).closest('.alert').fadeOut();
    });
});
</script>
<?php
// Captura o conteúdo do buffer para a variável $scripts e limpa o buffer.
$scripts = ob_get_clean();

// Adiciona a meta tag CSRF
$csrf_token_meta_tag = '<meta name="csrf-token" content="' . csrf_token() . '">';

// Incluir o layout com as variáveis definidas
include(resource_path("views/layout.php"));
?>
