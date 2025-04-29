<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit;
}

// Carregar estatísticas (exemplo simplificado)
use App\Models\Bidding;
use App\Models\Proposal;
use App\Models\Entity;

$totalBiddings = Bidding::count();
$openBiddings = Bidding::where('status', 'open')->count();
$totalProposals = Proposal::count();
$winningProposals = Proposal::where('status', 'winner')->count();
$totalEntities = Entity::count();

// Próximas licitações a fechar
$upcomingBiddings = Bidding::where('status', 'open')
    ->where('closing_date', '>', now())
    ->orderBy('closing_date', 'asc')
    ->take(5)
    ->with('entity')
    ->get();

// Últimas propostas
$recentProposals = Proposal::orderBy('created_at', 'desc')
    ->take(5)
    ->with('bidding')
    ->get();

?>
<div class="container-fluid py-4">
    <h1 class="mb-4">Dashboard</h1>

    <!-- Cards de Estatísticas -->
    <div class="row">
        <div class="col-md-3">
            <div class="card dashboard-card h-100" style="border-left-color: #3498db;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="card-title">Total de Licitações</h5>
                            <h2 class="mb-0"><?php echo $totalBiddings; ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-list-alt text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card h-100" style="border-left-color: #28a745;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="card-title">Licitações Abertas</h5>
                            <h2 class="mb-0"><?php echo $openBiddings; ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-door-open text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card h-100" style="border-left-color: #f39c12;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="card-title">Total de Propostas</h5>
                            <h2 class="mb-0"><?php echo $totalProposals; ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-file-contract text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card h-100" style="border-left-color: #e74c3c;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="card-title">Propostas Vencedoras</h5>
                            <h2 class="mb-0"><?php echo $winningProposals; ?></h2>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-trophy text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos e Tabelas -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Licitações a Vencer</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Entidade</th>
                                    <th>Data de Fechamento</th>
                                    <th>Valor Est.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($upcomingBiddings) > 0): ?>
                                    <?php foreach ($upcomingBiddings as $bidding): ?>
                                        <tr>
                                            <td>
                                                <a href="/biddings/<?php echo $bidding->id; ?>">
                                                    <?php echo htmlspecialchars($bidding->title); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($bidding->entity->name); ?></td>
                                            <td><?php echo $bidding->closing_date->format('d/m/Y H:i'); ?></td>
                                            <td>R$ <?php echo number_format($bidding->estimated_value, 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhuma licitação próxima do encerramento.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Últimas Propostas</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Licitação</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentProposals) > 0): ?>
                                    <?php foreach ($recentProposals as $proposal): ?>
                                        <tr>
                                            <td>
                                                <a href="/biddings/<?php echo $proposal->bidding->id; ?>">
                                                    <?php echo htmlspecialchars($proposal->bidding->title); ?>
                                                </a>
                                            </td>
                                            <td>R$ <?php echo number_format($proposal->proposed_value, 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo strtolower($proposal->status); ?>">
                                                    <?php echo $proposal->status; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $proposal->created_at->format('d/m/Y'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhuma proposta recente.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Performance -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Performance de Licitações</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    // Dados para o gráfico (seriam obtidos via AJAX em uma implementação real)
    const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    const biddingsData = [12, 19, 15, 17, 22, 25, 20, 18, 24, 29, 22, 18];
    const proposalsData = [18, 25, 20, 22, 30, 28, 25, 24, 30, 35, 28, 22];
    const winningData = [5, 8, 6, 5, 10, 12, 8, 7, 12, 15, 10, 8];

    // Configurar o gráfico
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Licitações',
                    data: biddingsData,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Propostas',
                    data: proposalsData,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Vencedoras',
                    data: winningData,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
