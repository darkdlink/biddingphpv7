<?php
// Inicia o buffer de saída para a variável $content
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Gerenciamento de Usuários</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">
            <i class="fas fa-user-plus"></i> Novo Usuário
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

<!-- Tabela de usuários -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Função</th>
                <th>Data Cadastro</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($usuarios) && (is_array($usuarios) || is_object($usuarios)) && count($usuarios) > 0): ?>
                <?php foreach($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario->id ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($usuario->name ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($usuario->email ?? 'N/A'); ?></td>
                    <td>
                        <span class="badge <?php echo (isset($usuario->admin) && $usuario->admin) ? "bg-primary" : "bg-info"; ?>">
                            <?php echo (isset($usuario->admin) && $usuario->admin) ? "Administrador" : "Usuário"; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        if (isset($usuario->created_at) && $usuario->created_at instanceof \DateTimeInterface) {
                            echo htmlspecialchars($usuario->created_at->format("d/m/Y H:i"));
                        } elseif (isset($usuario->created_at) && is_string($usuario->created_at)) {
                            try {
                                $date = new DateTime($usuario->created_at);
                                echo htmlspecialchars($date->format("d/m/Y H:i"));
                            } catch (Exception $e) {
                                echo 'Data inválida';
                            }
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="badge <?php echo (isset($usuario->ativo) && $usuario->ativo) ? "bg-success" : "bg-danger"; ?>">
                            <?php echo (isset($usuario->ativo) && $usuario->ativo) ? "Ativo" : "Inativo"; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning editar-usuario"
                                data-id="<?php echo htmlspecialchars($usuario->id ?? ''); ?>"
                                data-nome="<?php echo htmlspecialchars($usuario->name ?? ''); ?>"
                                data-email="<?php echo htmlspecialchars($usuario->email ?? ''); ?>"
                                data-admin="<?php echo isset($usuario->admin) && $usuario->admin ? '1' : '0'; ?>"
                                data-ativo="<?php echo isset($usuario->ativo) && $usuario->ativo ? '1' : '0'; ?>"
                                title="Editar Usuário">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger excluir-usuario"
                                data-id="<?php echo htmlspecialchars($usuario->id ?? ''); ?>"
                                title="Excluir Usuário">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhum usuário cadastrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Novo Usuário -->
<div class="modal fade" id="novoUsuarioModal" tabindex="-1" aria-labelledby="novoUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="novoUsuarioModalLabel">Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoUsuario">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="senha" name="password" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="confirmar_senha" class="form-label">Confirmar Senha <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmar_senha" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="admin" name="admin">
                        <label class="form-check-label" for="admin">Administrador</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoUsuario">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Usuário -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    <input type="hidden" id="editar_id" name="id">
                    <div class="mb-3">
                        <label for="editar_nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editar_nome" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="editar_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_senha" class="form-label">Nova Senha (deixe em branco para manter atual)</label>
                        <input type="password" class="form-control" id="editar_senha" name="password" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="editar_confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="editar_confirmar_senha" name="password_confirmation" autocomplete="new-password">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="editar_admin" name="admin">
                        <label class="form-check-label" for="editar_admin">Administrador</label>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="editar_ativo" name="ativo">
                        <label class="form-check-label" for="editar_ativo">Ativo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarEditarUsuario">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Excluir Usuário -->
<div class="modal fade" id="excluirUsuarioModal" tabindex="-1" aria-labelledby="excluirUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excluirUsuarioModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.</p>
                <input type="hidden" id="excluir_id_input"> <!-- Alterado ID para evitar conflito com variável -->
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
    var novoUsuarioModal = new bootstrap.Modal(document.getElementById('novoUsuarioModal'));
    var editarUsuarioModal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
    var excluirUsuarioModal = new bootstrap.Modal(document.getElementById('excluirUsuarioModal'));

    function showAlert(type, message, isHtml = false) {
        var alertId = type === 'success' ? '#alertSuccess' : '#alertError';
        var messageId = type === 'success' ? '#successMessage' : '#errorMessage';

        if (isHtml) {
            $(messageId).html(message);
        } else {
            $(messageId).text(message);
        }
        // Fecha outros alertas abertos
        type === 'success' ? $('#alertError').hide() : $('#alertSuccess').hide();

        $(alertId).fadeIn();
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $(alertId).fadeOut();
        }, 5000);
    }

    // Limpar formulário Novo Usuário ao fechar modal
    $('#novoUsuarioModal').on('hidden.bs.modal', function () {
        $('#formNovoUsuario')[0].reset();
        $('#alertError').hide(); // Esconde alertas de erro específicos do modal
    });

    // Limpar formulário Editar Usuário ao fechar modal
    $('#editarUsuarioModal').on('hidden.bs.modal', function () {
        $('#formEditarUsuario')[0].reset();
        $('#editar_senha, #editar_confirmar_senha').val(''); // Garante que senhas sejam limpas
        $('#alertError').hide();
    });


    // Abrir modal de edição
    $(document).on("click", ".editar-usuario", function() {
        var id = $(this).data("id");
        var nome = $(this).data("nome");
        var email = $(this).data("email");
        var admin = $(this).data("admin"); // Vem como '1' ou '0' (string)
        var ativo = $(this).data("ativo"); // Vem como '1' ou '0' (string)

        $("#editar_id").val(id);
        $("#editar_nome").val(nome);
        $("#editar_email").val(email);
        $("#editar_admin").prop("checked", admin == '1'); // Comparação não estrita
        $("#editar_ativo").prop("checked", ativo == '1'); // Comparação não estrita
        $("#editar_senha").val('');
        $("#editar_confirmar_senha").val('');

        editarUsuarioModal.show();
    });

    // Abrir modal de exclusão
    $(document).on("click", ".excluir-usuario", function() {
        var id = $(this).data("id");
        $("#excluir_id_input").val(id);
        excluirUsuarioModal.show();
    });

    // Salvar novo usuário
    $("#btnSalvarNovoUsuario").click(function() {
        if ($("#formNovoUsuario")[0].checkValidity()) {
            if ($("#senha").val() !== $("#confirmar_senha").val()) {
                showAlert('danger', "As senhas não conferem.");
                return;
            }

            var formData = {
                name: $("#nome").val(),
                email: $("#email").val(),
                password: $("#senha").val(),
                password_confirmation: $("#confirmar_senha").val(),
                admin: $("#admin").is(":checked") ? 1 : 0, // Usar .is(":checked")
                _token: $("meta[name=csrf-token]").attr("content")
            };

            $.ajax({
                url: "/usuarios", // Ajuste a URL conforme sua rota
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        novoUsuarioModal.hide();
                        showAlert('success', response.message || "Usuário criado com sucesso!");
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', response.message || "Erro ao criar usuário.");
                    }
                },
                error: function(xhr) {
                    var errors = "Erro ao criar usuário.";
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
                }
            });
        } else {
            $("#formNovoUsuario")[0].reportValidity();
        }
    });

    // Salvar edição de usuário
    $("#btnSalvarEditarUsuario").click(function() {
        if ($("#formEditarUsuario")[0].checkValidity()) {
            if ($("#editar_senha").val() !== "" && $("#editar_senha").val() !== $("#editar_confirmar_senha").val()) {
                showAlert('danger', "As novas senhas não conferem.");
                return;
            }

            var formData = {
                // id: $("#editar_id").val(), // O ID vai na URL
                name: $("#editar_nome").val(),
                email: $("#editar_email").val(),
                admin: $("#editar_admin").is(":checked") ? 1 : 0,
                ativo: $("#editar_ativo").is(":checked") ? 1 : 0,
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "PUT" // Método para Laravel/outros frameworks
            };

            if ($("#editar_senha").val() !== "") {
                formData.password = $("#editar_senha").val();
                formData.password_confirmation = $("#editar_confirmar_senha").val();
            }

            $.ajax({
                url: "/usuarios/" + $("#editar_id").val(), // Ajuste a URL conforme sua rota
                type: "POST", // Usar POST e _method: "PUT" para compatibilidade
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        editarUsuarioModal.hide();
                        showAlert('success', response.message || "Usuário atualizado com sucesso!");
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('danger', response.message || "Erro ao atualizar usuário.");
                    }
                },
                error: function(xhr) {
                    var errors = "Erro ao atualizar usuário.";
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
                }
            });
        } else {
            $("#formEditarUsuario")[0].reportValidity();
        }
    });

    // Confirmar exclusão de usuário
    $("#btnConfirmarExclusao").click(function() {
        var id = $("#excluir_id_input").val();

        $.ajax({
            url: "/usuarios/" + id, // Ajuste a URL conforme sua rota
            type: "POST", // Usar POST e _method: "DELETE"
            data: {
                _token: $("meta[name=csrf-token]").attr("content"),
                _method: "DELETE" // Método para Laravel/outros frameworks
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    excluirUsuarioModal.hide();
                    showAlert('success', response.message || "Usuário excluído com sucesso!");
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('danger', response.message || "Erro ao excluir usuário.");
                }
            },
            error: function(xhr) {
                var errorMsg = "Erro ao excluir usuário.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showAlert('danger', errorMsg);
            }
        });
    });

    // Ocultar alertas ao clicar no botão de fechar (caso não desapareçam sozinhos)
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
