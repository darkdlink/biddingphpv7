<?php
// Inicia o buffer de saída para a variável $content
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Configurações do Sistema</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" id="btnSalvarConfiguracoes">
            <i class="fas fa-save"></i> Salvar Alterações
        </button>
    </div>
</div>

<!-- Alerta de sucesso/erro -->
<div class="alert alert-success alert-dismissible fade show" role="alert" id="alertSuccess" style="display: none;">
    <span id="successMessage"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>

<div class="alert alert-danger alert-dismissible fade show" role="alert" id="alertError" style="display: none;">
    <span id="errorMessage"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Configurações da API</h5>
            </div>
            <div class="card-body">
                <form id="formConfiguracoesAPI">
                    <div class="mb-3">
                        <label for="api_url" class="form-label">URL da API do PNCP</label>
                        <input type="url" class="form-control" id="api_url" name="api_url" value="<?php echo htmlspecialchars($configuracoes['api_url'] ?? 'https://pncp.gov.br/api/consulta/v1'); ?>">
                        <div class="form-text">URL base para consulta à API do Portal Nacional de Contratações Públicas.</div>
                    </div>
                    <div class="mb-3">
                        <label for="api_timeout" class="form-label">Timeout da API (segundos)</label>
                        <input type="number" class="form-control" id="api_timeout" name="api_timeout" value="<?php echo htmlspecialchars(intval($configuracoes['api_timeout'] ?? 60)); ?>" min="10" max="300">
                        <div class="form-text">Tempo máximo de espera para respostas da API.</div>
                    </div>
                    <div class="mb-3">
                        <label for="itens_por_pagina" class="form-label">Itens por Página na Sincronização</label>
                        <input type="number" class="form-control" id="itens_por_pagina" name="itens_por_pagina" value="<?php echo htmlspecialchars(intval($configuracoes['itens_por_pagina'] ?? 20)); ?>" min="5" max="100">
                        <div class="form-text">Quantidade de licitações a serem buscadas por requisição.</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Configurações de Email</h5>
            </div>
            <div class="card-body">
                <form id="formConfiguracoesEmail">
                    <div class="mb-3">
                        <label for="email_driver" class="form-label">Driver de Email</label>
                        <select class="form-select" id="email_driver" name="email_driver">
                            <option value="smtp" <?php echo htmlspecialchars(($configuracoes['email_driver'] ?? 'smtp') == 'smtp' ? 'selected' : ''); ?>>SMTP</option>
                            <option value="sendmail" <?php echo htmlspecialchars(($configuracoes['email_driver'] ?? 'smtp') == 'sendmail' ? 'selected' : ''); ?>>Sendmail</option>
                            <option value="mailgun" <?php echo htmlspecialchars(($configuracoes['email_driver'] ?? 'smtp') == 'mailgun' ? 'selected' : ''); ?>>Mailgun</option>
                            <!-- Adicione outros drivers conforme necessário -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="email_host" class="form-label">Host SMTP</label>
                        <input type="text" class="form-control" id="email_host" name="email_host" value="<?php echo htmlspecialchars($configuracoes['email_host'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email_port" class="form-label">Porta</label>
                            <input type="number" class="form-control" id="email_port" name="email_port" value="<?php echo htmlspecialchars(intval($configuracoes['email_port'] ?? 587)); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email_encryption" class="form-label">Criptografia</label>
                            <select class="form-select" id="email_encryption" name="email_encryption">
                                <option value="tls" <?php echo htmlspecialchars(($configuracoes['email_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''); ?>>TLS</option>
                                <option value="ssl" <?php echo htmlspecialchars(($configuracoes['email_encryption'] ?? 'tls') == 'ssl' ? 'selected' : ''); ?>>SSL</option>
                                <option value="none" <?php echo htmlspecialchars(($configuracoes['email_encryption'] ?? 'tls') == 'none' ? 'selected' : ''); ?>>Nenhuma</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email_username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="email_username" name="email_username" value="<?php echo htmlspecialchars($configuracoes['email_username'] ?? ''); ?>" autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label for="email_password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="email_password" name="email_password" value="<?php echo htmlspecialchars($configuracoes['email_password'] ?? ''); ?>" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="email_from_address" class="form-label">Email de Origem</label>
                        <input type="email" class="form-control" id="email_from_address" name="email_from_address" value="<?php echo htmlspecialchars($configuracoes['email_from_address'] ?? 'noreply@example.com'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email_from_name" class="form-label">Nome do Remetente</label>
                        <input type="text" class="form-control" id="email_from_name" name="email_from_name" value="<?php echo htmlspecialchars($configuracoes['email_from_name'] ?? 'Sistema Bidding'); ?>">
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-info" id="btnTestarEmail"><i class="fas fa-paper-plane"></i> Testar Configurações de Email</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Configurações de Notificações</h5>
            </div>
            <div class="card-body">
                <form id="formConfiguracoesNotificacoes">
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="notificar_novas_licitacoes" name="notificar_novas_licitacoes" <?php echo htmlspecialchars(boolval($configuracoes['notificar_novas_licitacoes'] ?? false) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="notificar_novas_licitacoes">Notificar novas licitações</label>
                        <div class="form-text">Enviar email quando novas licitações forem encontradas.</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="notificar_encerramento" name="notificar_encerramento" <?php echo htmlspecialchars(boolval($configuracoes['notificar_encerramento'] ?? false) ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="notificar_encerramento">Notificar encerramento de prazos</label>
                        <div class="form-text">Enviar email quando estiver próximo do encerramento do prazo de licitações com interesse.</div>
                    </div>
                    <div class="mb-3">
                        <label for="dias_antecedencia" class="form-label">Dias de Antecedência</label>
                        <input type="number" class="form-control" id="dias_antecedencia" name="dias_antecedencia" value="<?php echo htmlspecialchars(intval($configuracoes['dias_antecedencia'] ?? 3)); ?>" min="1" max="15">
                        <div class="form-text">Quantidade de dias antes do encerramento para enviar notificação.</div>
                    </div>
                    <div class="mb-3">
                        <label for="emails_notificacao" class="form-label">Emails para Notificação</label>
                        <textarea class="form-control" id="emails_notificacao" name="emails_notificacao" rows="3"><?php echo htmlspecialchars($configuracoes['emails_notificacao'] ?? ''); ?></textarea>
                        <div class="form-text">Separe múltiplos emails com vírgula.</div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Configurações do Sistema</h5>
            </div>
            <div class="card-body">
                <form id="formConfiguracoesSistema">
                    <div class="mb-3">
                        <label for="nome_empresa" class="form-label">Nome da Empresa</label>
                        <input type="text" class="form-control" id="nome_empresa" name="nome_empresa" value="<?php echo htmlspecialchars($configuracoes['nome_empresa'] ?? 'Minha Empresa'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="cnpj_empresa" class="form-label">CNPJ da Empresa</label>
                        <input type="text" class="form-control" id="cnpj_empresa" name="cnpj_empresa" value="<?php echo htmlspecialchars($configuracoes['cnpj_empresa'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="logo_empresa_upload" class="form-label">Logo da Empresa</label>
                        <input type="file" class="form-control" id="logo_empresa_upload" name="logo_empresa_upload" accept="image/png, image/jpeg, image/gif">
                        <?php if (!empty($configuracoes['logo_empresa'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars(asset('storage/' . ($configuracoes['logo_empresa'] ?? ''))); ?>" alt="Logo da Empresa" class="img-thumbnail" style="max-height: 100px;">
                            <button type="button" class="btn btn-sm btn-danger ms-2" id="btnRemoverLogo"><i class="fas fa-trash"></i> Remover Logo</button>
                            <input type="hidden" name="remover_logo" id="remover_logo_input" value="0">
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="intervalo_sincronizacao" class="form-label">Intervalo de Sincronização Automática (horas)</label>
                        <select class="form-select" id="intervalo_sincronizacao" name="intervalo_sincronizacao">
                            <option value="0" <?php echo htmlspecialchars(intval($configuracoes['intervalo_sincronizacao'] ?? 0) == 0 ? 'selected' : ''); ?>>Desativado</option>
                            <option value="6" <?php echo htmlspecialchars(intval($configuracoes['intervalo_sincronizacao'] ?? 0) == 6 ? 'selected' : ''); ?>>6 horas</option>
                            <option value="12" <?php echo htmlspecialchars(intval($configuracoes['intervalo_sincronizacao'] ?? 0) == 12 ? 'selected' : ''); ?>>12 horas</option>
                            <option value="24" <?php echo htmlspecialchars(intval($configuracoes['intervalo_sincronizacao'] ?? 0) == 24 ? 'selected' : ''); ?>>24 horas</option>
                        </select>
                        <div class="form-text">Definir 0 para desativar a sincronização automática.</div>
                    </div>
                    <div class="mb-3">
                        <label for="tema" class="form-label">Tema do Sistema</label>
                        <select class="form-select" id="tema" name="tema">
                            <option value="light" <?php echo htmlspecialchars(($configuracoes['tema'] ?? 'light') == 'light' ? 'selected' : ''); ?>>Claro</option>
                            <option value="dark" <?php echo htmlspecialchars(($configuracoes['tema'] ?? 'light') == 'dark' ? 'selected' : ''); ?>>Escuro</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Backup do Sistema</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="btnGerarBackup">
                        <i class="fas fa-download"></i> Gerar Backup
                    </button>
                    <button type="button" class="btn btn-warning ms-2" id="btnAbrirModalRestaurar" data-bs-toggle="modal" data-bs-target="#restaurarBackupModal">
                        <i class="fas fa-upload"></i> Restaurar Backup
                    </button>
                </div>
                <div class="mb-3">
                    <p><strong>Último backup:</strong>
                    <?php
                    if (!empty($configuracoes['ultimo_backup'])) {
                        try {
                            $date = new DateTime($configuracoes['ultimo_backup']);
                            echo htmlspecialchars($date->format('d/m/Y H:i'));
                        } catch (Exception $e) {
                            echo 'Data inválida';
                        }
                    } else {
                        echo 'Nunca realizado';
                    }
                    ?>
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Arquivo</th>
                                <th>Data</th>
                                <th>Tamanho</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaBackupsBody">
                            <?php if(isset($backups) && count($backups) > 0): ?>
                                <?php foreach($backups as $backup): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($backup['nome'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php
                                        if (!empty($backup['data'])) {
                                            try {
                                                $date = new DateTime($backup['data']);
                                                echo htmlspecialchars($date->format('d/m/Y H:i'));
                                            } catch (Exception $e) {
                                                echo 'Data inválida';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($backup['tamanho'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="/configuracoes/backup/download/<?php echo htmlspecialchars($backup['id'] ?? ''); ?>" class="btn btn-sm btn-info" title="Baixar Backup">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger excluir-backup" data-id="<?php echo htmlspecialchars($backup['id'] ?? ''); ?>" title="Excluir Backup">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Nenhum backup encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Restaurar Backup -->
<div class="modal fade" id="restaurarBackupModal" tabindex="-1" aria-labelledby="restaurarBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restaurarBackupModalLabel">Restaurar Backup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Atenção!</strong> Restaurar um backup irá substituir todos os dados atuais. Esta ação não pode ser desfeita.
                </div>
                <form id="formRestaurarBackup" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="arquivo_backup_upload" class="form-label">Arquivo de Backup (.zip, .sql)</label>
                        <input type="file" class="form-control" id="arquivo_backup_upload" name="arquivo_backup_upload" accept=".zip,.sql" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarRestauracao">Restaurar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Backup -->
<div class="modal fade" id="excluirBackupModal" tabindex="-1" aria-labelledby="excluirBackupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excluirBackupModalLabel">Confirmar Exclusão de Backup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este backup? Esta ação não pode ser desfeita.</p>
                <input type="hidden" id="excluir_backup_id_input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoBackup">Excluir</button>
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
    var restaurarBackupModal = new bootstrap.Modal(document.getElementById('restaurarBackupModal'));
    var excluirBackupModal = new bootstrap.Modal(document.getElementById('excluirBackupModal'));

    function showAlert(type, message, isHtml = false) {
        var alertId = type === 'success' ? '#alertSuccess' : '#alertError';
        var messageId = type === 'success' ? '#successMessage' : '#errorMessage';

        if (isHtml) {
            $(messageId).html(message);
        } else {
            $(messageId).text(message);
        }
        type === 'success' ? $('#alertError').hide() : $('#alertSuccess').hide();
        $(alertId).fadeIn();
        setTimeout(function() { $(alertId).fadeOut(); }, 5000);
    }

    // Limpar formulário de restauração ao fechar modal
    $('#restaurarBackupModal').on('hidden.bs.modal', function () {
        $('#formRestaurarBackup')[0].reset();
    });

    // Salvar configurações
    $("#btnSalvarConfiguracoes").click(function() {
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Salvando...");

        var formData = new FormData();

        // Combina dados de todos os formulários de configuração
        $("#formConfiguracoesAPI, #formConfiguracoesEmail, #formConfiguracoesNotificacoes, #formConfiguracoesSistema").each(function() {
            $(this).find('input, select, textarea').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                if (name) {
                    if ($input.is(':checkbox')) {
                        formData.append(name, $input.is(':checked') ? 1 : 0);
                    } else if ($input.is(':file')) {
                        // O arquivo do logo é tratado separadamente abaixo para evitar conflito de nome
                        if (name !== 'logo_empresa_upload' && $input[0].files.length > 0) {
                           formData.append(name, $input[0].files[0]);
                        }
                    } else {
                        formData.append(name, $input.val());
                    }
                }
            });
        });

        // Adicionar arquivo de logo se selecionado
        if ($("#logo_empresa_upload")[0].files.length > 0) {
            formData.append("logo_empresa", $("#logo_empresa_upload")[0].files[0]); // Nome do campo para o backend
        }

        // Adicionar se o logo deve ser removido
        formData.append("remover_logo", $("#remover_logo_input").val());


        formData.append("_token", $("meta[name=csrf-token]").attr("content"));

        $.ajax({
            url: "/configuracoes", // Ajuste a URL da sua rota
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || "Configurações salvas com sucesso!");
                    // Se um novo logo foi salvo ou removido, pode ser necessário recarregar para ver a mudança
                    if (response.logo_updated) {
                        setTimeout(function() { location.reload(); }, 1500);
                    }
                } else {
                    showAlert('danger', response.message || "Erro ao salvar configurações.");
                }
            },
            error: function(xhr) {
                var errors = "Erro ao salvar configurações.";
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errors = "";
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errors += value.join("<br>") + "<br>";
                    });
                    showAlert('danger', errors, true);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                     showAlert('danger', xhr.responseJSON.message);
                } else {
                    showAlert('danger', errors);
                }
            },
            complete: function() {
                $this.prop("disabled", false).html("<i class='fas fa-save'></i> Salvar Alterações");
            }
        });
    });

    $("#btnRemoverLogo").click(function() {
        $("#remover_logo_input").val("1");
        $(this).closest("div").find("img").hide();
        $(this).hide();
        $("#logo_empresa_upload").val(''); // Limpa o campo de upload de arquivo
        showAlert('info', 'O logo será removido ao salvar as configurações.');
    });


    // Testar configurações de email
    $("#btnTestarEmail").click(function() {
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Testando...");
        var formData = $("#formConfiguracoesEmail").serializeArray();
        var dataToSend = {};
        $.each(formData, function(i, field){
            dataToSend[field.name] = field.value;
        });
        dataToSend._token = $("meta[name=csrf-token]").attr("content");

        $.ajax({
            url: "/configuracoes/testar-email", // Ajuste a URL
            type: "POST",
            data: dataToSend,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || "Email de teste enviado com sucesso! Verifique sua caixa de entrada.");
                } else {
                    showAlert('danger', response.message || "Falha ao enviar email de teste.");
                }
            },
            error: function(xhr) {
                showAlert('danger', "Erro ao conectar com o servidor para testar o email.");
            },
            complete: function() {
                 $this.prop("disabled", false).html("<i class='fas fa-paper-plane'></i> Testar Configurações de Email");
            }
        });
    });

    // Gerar backup
    $("#btnGerarBackup").click(function() {
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Gerando...");

        $.ajax({
            url: "/configuracoes/backup/gerar", // Ajuste a URL
            type: "POST",
            data: { _token: $("meta[name=csrf-token]").attr("content") },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message || "Backup gerado com sucesso!");
                    // Atualizar a lista de backups se necessário (exemplo: recarregar a seção)
                    // Ou simplesmente recarregar a página:
                    setTimeout(function() { location.reload(); }, 1500);
                } else {
                    showAlert('danger', response.message || "Erro ao gerar backup.");
                }
            },
            error: function(xhr) {
                showAlert('danger', "Erro ao conectar com o servidor para gerar o backup.");
            },
            complete: function() {
                $this.prop("disabled", false).html("<i class='fas fa-download'></i> Gerar Backup");
            }
        });
    });

    // Confirmar restauração de backup
    $("#btnConfirmarRestauracao").click(function() {
        if ($("#arquivo_backup_upload")[0].files.length === 0) {
            showAlert('danger', "Selecione um arquivo de backup.");
            return;
        }
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Restaurando...");

        var formData = new FormData();
        formData.append("arquivo_backup", $("#arquivo_backup_upload")[0].files[0]);
        formData.append("_token", $("meta[name=csrf-token]").attr("content"));

        $.ajax({
            url: "/configuracoes/backup/restaurar", // Ajuste a URL
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    restaurarBackupModal.hide();
                    showAlert('success', response.message || "Backup restaurado com sucesso! A página será recarregada.");
                    setTimeout(function() { location.reload(); }, 3000);
                } else {
                    showAlert('danger', response.message || "Erro ao restaurar backup.");
                }
            },
            error: function(xhr) {
                 showAlert('danger', "Erro ao conectar com o servidor para restaurar o backup.");
            },
            complete: function() {
                 $this.prop("disabled", false).html("Restaurar");
            }
        });
    });

    // Abrir modal de exclusão de backup
    $(document).on("click", ".excluir-backup", function() {
        var id = $(this).data("id");
        $("#excluir_backup_id_input").val(id);
        excluirBackupModal.show();
    });

    // Confirmar exclusão de backup
    $("#btnConfirmarExclusaoBackup").click(function() {
        var id = $("#excluir_backup_id_input").val();
        var $this = $(this);
        $this.prop("disabled", true).html("<i class='fas fa-spinner fa-spin'></i> Excluindo...");


        $.ajax({
            url: "/configuracoes/backup/excluir/" + id, // Ajuste a URL
            type: "POST", // Usando POST com _method: "DELETE"
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE"
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    excluirBackupModal.hide();
                    showAlert('success', response.message || "Backup excluído com sucesso!");
                    setTimeout(function() { location.reload(); }, 1500); // Recarrega para atualizar a lista
                } else {
                    showAlert('danger', response.message || "Erro ao excluir backup.");
                }
            },
            error: function(xhr) {
                showAlert('danger', "Erro ao conectar com o servidor para excluir o backup.");
            },
            complete: function() {
                $this.prop("disabled", false).html("Excluir");
            }
        });
    });

     // Ocultar alertas ao clicar no botão de fechar
    $(document).on('click', '.alert .btn-close', function() {
        $(this).closest('.alert').fadeOut();
    });
});
</script>
<?php
// Captura o conteúdo do buffer para a variável $scripts e limpa o buffer.
$scripts = ob_get_clean();

include(resource_path("views/layout.php")); // Certifique-se que este caminho está correto
?>
