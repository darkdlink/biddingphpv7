<?php
// Inicia o buffer de saída para a variável $content
ob_start();

// Definir valores padrão para as variáveis de ordenação
$sortField = $sortField ?? request()->get('sort', 'created_at');
$sortDirection = $sortDirection ?? request()->get('direction', 'desc');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Licitações por Segmento de Negócio</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-primary me-2" id="btnSincronizar">
            <i class="fas fa-sync"></i> Sincronizar Licitações
        </button>
    </div>
</div>

<!-- Mensagens de alerta -->
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

<!-- Cartões de estatísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total de Licitações</h5>
                <h2 class="card-text"><?php echo isset($licitacoes) ? $licitacoes->total() : 0; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Segmento Atual</h5>
                <h2 class="card-text"><?php echo isset($filtros['segmento_nome']) ? $filtros['segmento_nome'] : 'Todos'; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Encerram em 7 dias</h5>
                <h2 class="card-text"><?php 
                    echo isset($licitacoes_proximas) ? $licitacoes_proximas : 0;
                ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Com Interesse</h5>
                <h2 class="card-text"><?php 
                    echo isset($licitacoes_interesse) ? $licitacoes_interesse : 0;
                ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Seletor de Segmento e Filtros -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Selecione o Segmento da sua Empresa</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?php echo route('licitacoes.segmento'); ?>" id="filtroSegmentoForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas as Categorias</option>
                        <option value="1" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '1' ? 'selected' : ''; ?>>Construção e Engenharia</option>
                        <option value="2" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '2' ? 'selected' : ''; ?>>Fornecimento de Materiais e Produtos</option>
                        <option value="3" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '3' ? 'selected' : ''; ?>>Serviços Gerais e Especializados</option>
                        <option value="4" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '4' ? 'selected' : ''; ?>>Tecnologia da Informação e Comunicação (TIC)</option>
                        <option value="5" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '5' ? 'selected' : ''; ?>>Consultoria e Assessoria</option>
                        <option value="6" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '6' ? 'selected' : ''; ?>>Saúde e Bem-Estar</option>
                        <option value="7" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '7' ? 'selected' : ''; ?>>Educação e Treinamento</option>
                        <option value="8" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '8' ? 'selected' : ''; ?>>Meio Ambiente e Sustentabilidade</option>
                        <option value="9" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '9' ? 'selected' : ''; ?>>Agricultura, Pecuária e Pesca</option>
                        <option value="10" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '10' ? 'selected' : ''; ?>>Cultura, Lazer e Turismo</option>
                        <option value="11" <?php echo isset($filtros['categoria']) && $filtros['categoria'] == '11' ? 'selected' : ''; ?>>Indústria (Transformação)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="segmento" class="form-label">Segmento Específico</label>
                    <select class="form-select" id="segmento" name="segmento">
                        <option value="">Selecione uma categoria primeiro</option>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="uf" class="form-label">UF</label>
                    <select name="uf" id="uf" class="form-select">
                        <option value="">Todos os estados</option>
                        <option value="AC" <?php echo isset($filtros['uf']) && $filtros['uf'] == 'AC' ? 'selected' : ''; ?>>AC</option>
                        <option value="AL" <?php echo isset($filtros['uf']) && $filtros['uf'] == 'AL' ? 'selected' : ''; ?>>AL</option>
                        <!-- Continue com todos os estados -->
                        <option value="SP" <?php echo isset($filtros['uf']) && $filtros['uf'] == 'SP' ? 'selected' : ''; ?>>SP</option>
                        <option value="TO" <?php echo isset($filtros['uf']) && $filtros['uf'] == 'TO' ? 'selected' : ''; ?>>TO</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="modalidade" class="form-label">Modalidade</label>
                    <select name="modalidade" id="modalidade" class="form-select">
                        <option value="">Todas</option>
                        <option value="Pregão" <?php echo isset($filtros['modalidade']) && $filtros['modalidade'] == 'Pregão' ? 'selected' : ''; ?>>Pregão</option>
                        <option value="Concorrência" <?php echo isset($filtros['modalidade']) && $filtros['modalidade'] == 'Concorrência' ? 'selected' : ''; ?>>Concorrência</option>
                        <option value="Tomada de Preços" <?php echo isset($filtros['modalidade']) && $filtros['modalidade'] == 'Tomada de Preços' ? 'selected' : ''; ?>>Tomada de Preços</option>
                        <option value="Convite" <?php echo isset($filtros['modalidade']) && $filtros['modalidade'] == 'Convite' ? 'selected' : ''; ?>>Convite</option>
                        <option value="Dispensa" <?php echo isset($filtros['modalidade']) && $filtros['modalidade'] == 'Dispensa' ? 'selected' : ''; ?>>Dispensa</option>
                        <option value="Inexigibilidade" <?php echo isset($filtros['modalidade']) && $filtros['modalidade'] == 'Inexigibilidade' ? 'selected' : ''; ?>>Inexigibilidade</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="data_min" class="form-label">Data Mínima</label>
                    <input type="date" class="form-control" id="data_min" name="data_min" 
                           value="<?php echo isset($filtros['data_min']) ? $filtros['data_min'] : ''; ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="valor_min" class="form-label">Valor Mínimo (R$)</label>
                    <input type="number" class="form-control" id="valor_min" name="valor_min" 
                           value="<?php echo isset($filtros['valor_min']) ? $filtros['valor_min'] : ''; ?>">
                </div>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrar Licitações
                </button>
                <a href="<?php echo route('licitacoes.segmento'); ?>" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Limpar Filtros
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Listagem de Licitações -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Licitações Encontradas para seu Segmento</h5>
    </div>
    <div class="card-body">
        <?php if(isset($licitacoes) && count($licitacoes) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="<?php echo route('licitacoes.segmento', array_merge($filtros, ['sort' => 'id', 'direction' => $sortField == 'id' && $sortDirection == 'asc' ? 'desc' : 'asc'])); ?>">
                                    ID 
                                    <?php if ($sortField == 'id'): ?>
                                        <i class="fas fa-sort-<?php echo $sortDirection == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Número</th>
                            <th>Objeto</th>
                            <th>Órgão</th>
                            <th>UF</th>
                            <th>Modalidade</th>
                            <th>Valor (R$)</th>
                            <th>
                                <a href="<?php echo route('licitacoes.segmento', array_merge($filtros, ['sort' => 'data_encerramento_proposta', 'direction' => $sortField == 'data_encerramento_proposta' && $sortDirection == 'asc' ? 'desc' : 'asc'])); ?>">
                                    Data Encerramento 
                                    <?php if ($sortField == 'data_encerramento_proposta'): ?>
                                        <i class="fas fa-sort-<?php echo $sortDirection == 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Interesse</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($licitacoes as $licitacao): ?>
                            <?php 
                                // Verificar se está próximo do vencimento (7 dias)
                                $isProximoVencimento = false;
                                if (!empty($licitacao->data_encerramento_proposta)) {
                                    $dataEncerramento = \Carbon\Carbon::parse($licitacao->data_encerramento_proposta);
                                    $hoje = \Carbon\Carbon::now();
                                    $diasFaltando = $hoje->diffInDays($dataEncerramento, false);
                                    if ($diasFaltando >= 0 && $diasFaltando <= 7) {
                                        $isProximoVencimento = true;
                                    }
                                }
                                
                                // Destacar com base no segmento selecionado
                                $isSegmentoDestacado = isset($filtros['segmento']) && !empty($filtros['segmento']);
                            ?>
                            <tr class="<?php echo $isProximoVencimento ? 'table-warning' : ''; ?> <?php echo $isSegmentoDestacado ? 'table-success' : ''; ?>">
                                <td><?php echo htmlspecialchars($licitacao->id ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($licitacao->numero_compra ?? 'N/A'); ?></td>
                                <td>
                                    <?php 
                                        // Exibir apenas os primeiros 100 caracteres do objeto com palavras-chave destacadas
                                        $objeto = $licitacao->objeto_compra ?? 'N/A';
                                        $objeto = mb_strlen($objeto) > 100 ? mb_substr($objeto, 0, 100) . '...' : $objeto;
                                        
                                        // Destacar palavras-chave se houver um segmento selecionado
                                        if (isset($palavras_chave) && !empty($palavras_chave)) {
                                            foreach ($palavras_chave as $palavra) {
                                                $objeto = preg_replace('/(' . preg_quote($palavra, '/') . ')/i', '<span class="bg-success text-white">$1</span>', $objeto);
                                            }
                                        }
                                        
                                        echo $objeto;
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($licitacao->orgao_entidade ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($licitacao->uf ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($licitacao->modalidade_nome ?? 'N/A'); ?></td>
                                <td><?php echo 'R$ ' . number_format($licitacao->valor_total_estimado ?? 0, 2, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                        if (!empty($licitacao->data_encerramento_proposta)) {
                                            $dataEncerramento = \Carbon\Carbon::parse($licitacao->data_encerramento_proposta);
                                            echo htmlspecialchars($dataEncerramento->format('d/m/Y'));
                                            
                                            // Adicionar indicador de dias restantes
                                            $hoje = \Carbon\Carbon::now();
                                            $diasFaltando = $hoje->diffInDays($dataEncerramento, false);
                                            
                                            if ($diasFaltando < 0) {
                                                echo ' <span class="badge bg-danger">Encerrado</span>';
                                            } elseif ($diasFaltando == 0) {
                                                echo ' <span class="badge bg-danger">Hoje</span>';
                                            } elseif ($diasFaltando <= 3) {
                                                echo ' <span class="badge bg-danger">' . $diasFaltando . ' dias</span>';
                                            } elseif ($diasFaltando <= 7) {
                                                echo ' <span class="badge bg-warning text-dark">' . $diasFaltando . ' dias</span>';
                                            } else {
                                                echo ' <span class="badge bg-info">' . $diasFaltando . ' dias</span>';
                                            }
                                        } else {
                                            echo 'N/A';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-interesse" 
                                               type="checkbox" 
                                               data-id="<?php echo $licitacao->id; ?>" 
                                               <?php echo $licitacao->interesse ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo route('licitacoes.show', $licitacao->id); ?>" class="btn btn-sm btn-info" title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if(!empty($licitacao->link_sistema_origem)): ?>
                                            <a href="<?php echo $licitacao->link_sistema_origem; ?>" target="_blank" class="btn btn-sm btn-primary" title="Acessar no PNCP">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo $licitacoes->appends($filtros)->links(); ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Nenhuma licitação encontrada para os filtros selecionados.
            </div>
        <?php endif; ?>
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
    // Dados para popular o select de segmentos
    const segmentosPorCategoria = {
        '1': [ // Construção e Engenharia
            {id: '1_1', nome: 'Construção Civil'},
            {id: '1_2', nome: 'Obras de Infraestrutura'},
            {id: '1_3', nome: 'Engenharia Elétrica'},
            {id: '1_4', nome: 'Engenharia Hidráulica e Sanitária'},
            {id: '1_5', nome: 'Engenharia Mecânica'},
            {id: '1_6', nome: 'Serviços de Arquitetura e Urbanismo'},
            {id: '1_7', nome: 'Serviços de Topografia e Agrimensura'},
            {id: '1_8', nome: 'Demolição e Terraplanagem'},
            {id: '1_9', nome: 'Reformas e Manutenção Predial'},
            {id: '1_10', nome: 'Instalação de Sistemas de Segurança'},
            {id: '1_11', nome: 'Pavimentação e Asfaltamento'},
            {id: '1_12', nome: 'Consultoria em Engenharia'}
        ],
        '2': [ // Fornecimento de Materiais e Produtos
            {id: '2_1', nome: 'Materiais de Construção'},
            {id: '2_2', nome: 'Material de Escritório e Papelaria'},
            {id: '2_3', nome: 'Equipamentos de Informática (Hardware)'},
            {id: '2_4', nome: 'Mobiliário'},
            {id: '2_5', nome: 'Produtos Químicos'},
            {id: '2_6', nome: 'Produtos de Limpeza e Higiene'},
            {id: '2_7', nome: 'Equipamentos de Proteção Individual (EPI)'},
            {id: '2_8', nome: 'Uniformes e Vestuário Profissional'},
            {id: '2_9', nome: 'Veículos e Peças Automotivas'},
            {id: '2_10', nome: 'Alimentos e Bebidas'},
            {id: '2_11', nome: 'Medicamentos e Materiais Hospitalares'},
            {id: '2_12', nome: 'Livros e Material Didático'},
            {id: '2_13', nome: 'Equipamentos Audiovisuais'},
            {id: '2_14', nome: 'Equipamentos de Cozinha Industrial'},
            {id: '2_15', nome: 'Combustíveis e Lubrificantes'},
            {id: '2_16', nome: 'Produtos Agrícolas e Agropecuários'},
            {id: '2_17', nome: 'Software e Licenças de Uso'}
        ],
        '3': [ // Serviços Gerais e Especializados
            {id: '3_1', nome: 'Serviços de Limpeza e Conservação'},
            {id: '3_2', nome: 'Serviços de Vigilância e Segurança Patrimonial'},
            {id: '3_3', nome: 'Serviços de Portaria e Recepção'},
            {id: '3_4', nome: 'Jardinagem e Paisagismo'},
            {id: '3_5', nome: 'Manutenção de Equipamentos'},
            {id: '3_6', nome: 'Transporte e Logística'},
            {id: '3_7', nome: 'Serviços Gráficos e de Impressão'},
            {id: '3_8', nome: 'Alimentação'},
            {id: '3_9', nome: 'Hotelaria e Hospedagem'},
            {id: '3_10', nome: 'Organização de Eventos'},
            {id: '3_11', nome: 'Serviços de Telecomunicações'},
            {id: '3_12', nome: 'Dedetização e Controle de Pragas'},
            {id: '3_13', nome: 'Coleta e Gerenciamento de Resíduos'},
            {id: '3_14', nome: 'Lavanderia'},
            {id: '3_15', nome: 'Serviços de Tradução e Interpretação'},
            {id: '3_16', nome: 'Locação de Equipamentos'},
            {id: '3_17', nome: 'Locação de Veículos'},
            {id: '3_18', nome: 'Serviços de Call Center e Telemarketing'}
        ],
        '4': [ // Tecnologia da Informação e Comunicação
            {id: '4_1', nome: 'Desenvolvimento de Software e Sistemas'},
            {id: '4_2', nome: 'Consultoria em TI'},
            {id: '4_3', nome: 'Serviços de Suporte Técnico e Help Desk'},
            {id: '4_4', nome: 'Infraestrutura de Redes e Cabeamento'},
            {id: '4_5', nome: 'Segurança da Informação e Cibersegurança'},
            {id: '4_6', nome: 'Serviços de Cloud Computing'},
            {id: '4_7', nome: 'Desenvolvimento de Aplicativos Móveis'},
            {id: '4_8', nome: 'Web Design e Desenvolvimento Web'},
            {id: '4_9', nome: 'Marketing Digital e SEO'},
            {id: '4_10', nome: 'Gestão de Mídias Sociais'},
            {id: '4_11', nome: 'Treinamento em TI'}
        ],
        '5': [ // Consultoria e Assessoria
            {id: '5_1', nome: 'Consultoria Empresarial e de Gestão'},
            {id: '5_2', nome: 'Consultoria Financeira e Contábil'},
            {id: '5_3', nome: 'Consultoria Jurídica'},
            {id: '5_4', nome: 'Consultoria Ambiental'},
            {id: '5_5', nome: 'Consultoria em Recursos Humanos'},
            {id: '5_6', nome: 'Auditoria'},
            {id: '5_7', nome: 'Consultoria em Marketing e Vendas'},
            {id: '5_8', nome: 'Assessoria de Imprensa e Comunicação'}
        ],
        '6': [ // Saúde e Bem-Estar
            {id: '6_1', nome: 'Serviços Médicos e Odontológicos'},
            {id: '6_2', nome: 'Serviços de Enfermagem'},
            {id: '6_3', nome: 'Fisioterapia e Reabilitação'},
            {id: '6_4', nome: 'Serviços Laboratoriais e de Diagnóstico'},
            {id: '6_5', nome: 'Psicologia e Terapia'},
            {id: '6_6', nome: 'Gestão de Planos de Saúde'}
        ],
        '7': [ // Educação e Treinamento
            {id: '7_1', nome: 'Cursos e Treinamentos Corporativos'},
            {id: '7_2', nome: 'Capacitação Profissional'},
            {id: '7_3', nome: 'Ensino de Idiomas'},
            {id: '7_4', nome: 'Consultoria Educacional'},
            {id: '7_5', nome: 'Produção de Conteúdo Educacional'}
        ],
        '8': [ // Meio Ambiente e Sustentabilidade
            {id: '8_1', nome: 'Licenciamento Ambiental'},
            {id: '8_2', nome: 'Estudos de Impacto Ambiental'},
            {id: '8_3', nome: 'Projetos de Recuperação de Áreas Degradadas'},
            {id: '8_4', nome: 'Gestão de Resíduos Sólidos e Reciclagem'},
            {id: '8_5', nome: 'Energias Renováveis'},
            {id: '8_6', nome: 'Consultoria em Sustentabilidade'}
        ],
        '9': [ // Agricultura, Pecuária e Pesca
            {id: '9_1', nome: 'Produção Agrícola'},
            {id: '9_2', nome: 'Criação de Animais'},
            {id: '9_3', nome: 'Aquicultura e Pesca'},
            {id: '9_4', nome: 'Serviços Veterinários'},
            {id: '9_5', nome: 'Fornecimento de Insumos Agrícolas'}
        ],
        '10': [ // Cultura, Lazer e Turismo
            {id: '10_1', nome: 'Produção Cultural e Artística'},
            {id: '10_2', nome: 'Serviços Turísticos e Agências de Viagem'},
            {id: '10_3', nome: 'Organização de Atividades de Lazer e Recreação'}
        ],
        '11': [ // Indústria (Transformação)
            {id: '11_1', nome: 'Indústria Metalúrgica'},
            {id: '11_2', nome: 'Indústria Têxtil e de Confecção'},
            {id: '11_3', nome: 'Indústria Moveleira'},
            {id: '11_4', nome: 'Indústria Farmacêutica'},
            {id: '11_5', nome: 'Indústria de Alimentos e Bebidas'},
            {id: '11_6', nome: 'Indústria Gráfica e Editorial'},
            {id: '11_7', nome: 'Indústria de Plásticos e Borracha'}
        ]
    };

    // Função para carregar os segmentos com base na categoria selecionada
    function carregarSegmentos() {
        const categoriaId = $('#categoria').val();
        const $segmentoSelect = $('#segmento');
        
        // Limpar select de segmentos
        $segmentoSelect.empty();
        $segmentoSelect.append('<option value="">Selecione um segmento</option>');
        

    // Continuando do código anterior
    
    // Se uma categoria foi selecionada
    if (categoriaId) {
        const segmentos = segmentosPorCategoria[categoriaId] || [];
        
        // Preencher o select com os segmentos da categoria
        segmentos.forEach(function(segmento) {
            const selected = "<?php echo isset($filtros['segmento']) ? $filtros['segmento'] : ''; ?>" === segmento.id ? 'selected' : '';
            $segmentoSelect.append(`<option value="${segmento.id}" ${selected}>${segmento.nome}</option>`);
        });
    }
}

// Evento de mudança de categoria
$('#categoria').on('change', function() {
    carregarSegmentos();
});

// Carregar segmentos iniciais se houver categoria selecionada
carregarSegmentos();

// Toggle de interesse
$('.toggle-interesse').on('change', function() {
    const id = $(this).data('id');
    const interesse = $(this).prop('checked');
    const $this = $(this);
    
    $.ajax({
        url: `<?php echo url('/licitacoes'); ?>/${id}/interesse`,
        type: 'POST',
        data: {
            interesse: interesse ? 1 : 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
            } else {
                $this.prop('checked', !interesse);
                showAlert('error', response.message || 'Erro ao atualizar status de interesse.');
            }
        },
        error: function() {
            $this.prop('checked', !interesse);
            showAlert('error', 'Erro ao comunicar com o servidor.');
        }
    });
});

// Função para mostrar alertas
function showAlert(type, message) {
    const alertId = type === 'success' ? '#alertSuccess' : '#alertError';
    const messageId = type === 'success' ? '#successMessage' : '#errorMessage';
    
    $(messageId).text(message);
    $(alertId).fadeIn();
    
    setTimeout(function() {
        $(alertId).fadeOut();
    }, 5000);
}

// Botão de sincronização
$('#btnSincronizar').on('click', function() {
    const $this = $(this);
    $this.prop('disabled', true).html('<i class="fas fa-sync fa-spin"></i> Sincronizando...');
    
    // Obter segmento selecionado para sincronizar licitações relacionadas
    const categoriaId = $('#categoria').val();
    const segmentoId = $('#segmento').val();
    
    // Parâmetros adicionais para a sincronização
    const params = {
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    if (segmentoId) {
        params.segmento = segmentoId;
    } else if (categoriaId) {
        params.categoria = categoriaId;
    }
    
    $.ajax({
        url: '<?php echo route('licitacoes.sincronizar.segmento'); ?>',
        type: 'POST',
        data: params,
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                showAlert('error', response.message || 'Erro na sincronização.');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Erro ao sincronizar licitações.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            showAlert('error', errorMsg);
        },
        complete: function() {
            $this.prop('disabled', false).html('<i class="fas fa-sync"></i> Sincronizar Licitações');
        }
    });
});

// Destaque de relevância
function destacarRelevancia() {
    // Obter segmento e palavras-chave
    const segmentoId = $('#segmento').val();
    if (!segmentoId) return;
    
    // Mapear palavras-chave por segmento (exemplo simplificado)
    const palavrasChavePorSegmento = {
        // Construção Civil
        '1_1': ['construção', 'edificação', 'prédio', 'residencial', 'comercial', 'reforma'],
        // Obras de Infraestrutura
        '1_2': ['infraestrutura', 'estrada', 'ponte', 'saneamento', 'porto', 'aeroporto'],
        // Serviços de Limpeza
        '3_1': ['limpeza', 'conservação', 'higienização', 'serviços gerais'],
        // Desenvolvimento de Software
        '4_1': ['software', 'desenvolvimento', 'sistema', 'programação', 'aplicativo'],
        // Meio Ambiente
        '8_1': ['ambiental', 'licenciamento', 'meio ambiente', 'sustentabilidade']
        // Adicionar mais conforme necessário
    };
    
    const palavrasChave = palavrasChavePorSegmento[segmentoId] || [];
    if (palavrasChave.length === 0) return;
    
    // Destacar nas descrições de licitações
    $('.table tbody tr').each(function() {
        const $this = $(this);
        const $objetoCell = $this.find('td:nth-child(3)');
        const objeto = $objetoCell.text();
        
        // Verificar se alguma palavra-chave está presente
        let relevancia = 0;
        let textoDestacado = objeto;
        
        palavrasChave.forEach(function(palavra) {
            const regex = new RegExp(`(${palavra})`, 'gi');
            if (regex.test(objeto)) {
                relevancia++;
                textoDestacado = textoDestacado.replace(regex, '<span class="highlight">$1</span>');
            }
        });
        
        // Aplicar destaque com base na relevância
        if (relevancia > 0) {
            $objetoCell.html(textoDestacado);
            
            // Adicionar classe de destaque com base na relevância
            if (relevancia >= 3) {
                $this.addClass('table-success');
            } else if (relevancia === 2) {
                $this.addClass('table-info');
            } else {
                $this.addClass('table-light');
            }
            
            // Adicionar badge de relevância
            const $badgeCell = $this.find('td:nth-child(1)');
            $badgeCell.append(`<span class="badge bg-success ms-2">${relevancia}</span>`);
        }
    });
}

// Executar destaque inicial
destacarRelevancia();

// Ao trocar de segmento, atualizar destaques
$('#segmento').on('change', function() {
    // Limpar destaques anteriores
    $('.table tbody tr').removeClass('table-success table-info table-light');
    $('.table tbody tr td:nth-child(1) .badge').remove();
    $('.table tbody tr td:nth-child(3) .highlight').each(function() {
        $(this).replaceWith($(this).text());
    });
    
    // Aplicar novos destaques
    destacarRelevancia();
});

});
</script>

<style>
/* Estilos adicionais para melhorar a visualização */
.highlight {
    background-color: #ffc107;
    font-weight: bold;
    padding: 0 2px;
    border-radius: 3px;
}

.segmento-card {
    transition: all 0.3s;
    cursor: pointer;
}

.segmento-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.segmento-card.selected {
    border: 2px solid #007bff;
    background-color: rgba(0,123,255,0.1);
}

.badge-relevancia {
    position: absolute;
    top: -10px;
    right: -10px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
<?php
// Captura o conteúdo do buffer para a variável $scripts e limpa o buffer.
$scripts = ob_get_clean();

// Adiciona a meta tag CSRF
$csrf_token_meta_tag = '<meta name="csrf-token" content="' . csrf_token() . '">';

// Incluir o layout com as variáveis definidas
include(resource_path("views/layout.php"));
?>