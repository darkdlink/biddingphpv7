<?php
$content = '
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Teste de Conexão com API</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Configuração da API</h5>
            </div>
            <div class="card-body">
                <form id="apiTestForm">
                    <div class="mb-3">
                        <label for="base_url" class="form-label">URL Base</label>
                        <input type="text" class="form-control" id="base_url" name="base_url" value="https://pncp.gov.br/api/consulta/v1">
                    </div>
                    <div class="mb-3">
                        <label for="endpoint" class="form-label">Endpoint</label>
                        <input type="text" class="form-control" id="endpoint" name="endpoint" value="/contratacoes/proposta">
                    </div>
                    <div class="mb-3">
                        <label for="data_final" class="form-label">Data Final (formato YYYYMMDD)</label>
                        <input type="text" class="form-control" id="data_final" name="data_final" value="' . date('Ymd') . '">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pagina" class="form-label">Página</label>
                            <input type="number" class="form-control" id="pagina" name="pagina" value="1" min="1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tamanho_pagina" class="form-label">Itens por Página</label>
                            <input type="number" class="form-control" id="tamanho_pagina" name="tamanho_pagina" value="10" min="1" max="100">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="btnTestarAPI">
                            <i class="fas fa-play"></i> Testar Conexão
                        </button>
                        <a href="/api-test/specific-url" class="btn btn-warning">
                            <i class="fas fa-link"></i> Testar URL Específica
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Status da Conexão</h5>
            </div>
            <div class="card-body">
                <div id="statusLoading" style="display: none;">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                    <p class="text-center mt-2">Testando conexão com a API...</p>
                </div>

                <div id="statusResult" style="display: none;">
                    <div id="statusSuccess" class="alert alert-success" style="display: none;">
                        <h5><i class="fas fa-check-circle"></i> Conexão bem-sucedida!</h5>
                        <p id="successMessage"></p>
                    </div>

                    <div id="statusError" class="alert alert-danger" style="display: none;">
                        <h5><i class="fas fa-exclamation-circle"></i> Erro na conexão!</h5>
                        <p id="errorMessage"></p>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Detalhes da Requisição</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">URL Completa:</dt>
                                <dd class="col-sm-8" id="reqUrl"></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Detalhes da Resposta</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Código de Status:</dt>
                                <dd class="col-sm-8" id="resStatus"></dd>

                                <dt class="col-sm-4">Tempo de Resposta:</dt>
                                <dd class="col-sm-8" id="resTime"></dd>

                                <dt class="col-sm-4">JSON Válido:</dt>
                                <dd class="col-sm-8" id="resJsonValid"></dd>

                                <dt class="col-sm-4">Contagem de Itens:</dt>
                                <dd class="col-sm-8" id="resDataCount"></dd>

                                <dt class="col-sm-4">Total de Registros:</dt>
                                <dd class="col-sm-8" id="resTotalRecords"></dd>
                            </dl>
                        </div>
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
    $("#btnTestarAPI").click(function() {
        // Exibir loading
        $("#statusLoading").show();
        $("#statusResult").hide();

        // Obter dados do formulário
        var formData = {
            base_url: $("#base_url").val(),
            endpoint: $("#endpoint").val(),
            data_final: $("#data_final").val(),
            pagina: $("#pagina").val(),
            tamanho_pagina: $("#tamanho_pagina").val()
        };

        // Enviar requisição AJAX
        $.ajax({
            url: "/api-test/connection",
            type: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content")
            },
            dataType: "json",
            success: function(response) {
                // Ocultar loading
                $("#statusLoading").hide();
                $("#statusResult").show();

                if (response.success) {
                    // Exibir sucesso
                    $("#statusSuccess").show();
                    $("#statusError").hide();
                    $("#successMessage").text("A conexão com a API foi estabelecida com sucesso. Código de status: " + response.status_code);
                } else {
                    // Exibir erro
                    $("#statusSuccess").hide();
                    $("#statusError").show();
                    $("#errorMessage").text(response.message);
                }

                // Preencher detalhes da requisição
                $("#reqUrl").text(response.request_details.url);

                // Preencher detalhes da resposta
                $("#resStatus").text(response.status_code);
                $("#resTime").text(response.response_time);
                $("#resJsonValid").text(response.json_valid ? "Sim" : "Não");
                $("#resDataCount").text(response.data_count);
                $("#resTotalRecords").text(response.total_records);
            },
            error: function(xhr, status, error) {
                // Ocultar loading
                $("#statusLoading").hide();
                $("#statusResult").show();

                // Exibir erro
                $("#statusSuccess").hide();
                $("#statusError").show();

                var errorMessage = "";
                try {
                    var response = JSON.parse(xhr.responseText);
                    errorMessage = response.message + ": " + response.error;
                } catch (e) {
                    errorMessage = "Erro ao processar a requisição: " + error;
                }

                $("#errorMessage").text(errorMessage);
            },
            timeout: 30000 // 30 segundos
        });
    });
});
</script>
';

include(resource_path("views/layout.php"));
