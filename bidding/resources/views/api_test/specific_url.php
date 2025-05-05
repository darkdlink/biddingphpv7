<?php
$content = '
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Teste com URL Específica</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/api-test" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">URL Testada</h5>
            </div>
            <div class="card-body">
                <code>' . ($url ?? 'N/A') . '</code>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title">Resultado do Teste</h5>
            </div>
            <div class="card-body">
                ' . (isset($success) && $success ? '
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle"></i> Conexão bem-sucedida!</h5>
                    <p>A URL foi acessada com sucesso.</p>
                </div>
                ' : '
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-circle"></i> Erro na conexão!</h5>
                    <p>' . (isset($error) ? htmlspecialchars($error) : 'Erro desconhecido') . '</p>
                </div>
                ') . '

                <h6 class="mt-4 mb-3">Detalhes da Resposta</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">Código de Status</th>
                                <td>' . (isset($status_code) ? $status_code : 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>Tempo de Resposta</th>
                                <td>' . (isset($response_time) ? $response_time . 'ms' : 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>JSON Válido</th>
                                <td>' . (isset($json_valid) ? ($json_valid ? 'Sim' : 'Não') : 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>Contagem de Itens</th>
                                <td>' . (isset($data_count) ? $data_count : 'N/A') . '</td>
                            </tr>
                            <tr>
                                <th>Total de Registros</th>
                                <td>' . (isset($total_records) ? $total_records : 'N/A') . '</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        ' . (isset($response_preview) && !empty($response_preview) ? '
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Prévia da Resposta</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">' . htmlspecialchars($response_preview) . '</pre>
            </div>
        </div>
        ' : '') . '
    </div>
</div>
';

include(resource_path("views/layout.php"));
