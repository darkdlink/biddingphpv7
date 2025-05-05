<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php
    use Illuminate\Support\Facades\Auth;
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Bidding - Sistema de Capitalização de Licitações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #f8f9fa;
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .nav-link {
            font-weight: 500;
            color: #333;
        }

        .nav-link.active {
            color: #007bff;
        }

        main {
            padding-top: 48px;
        }

        .navbar-brand {
            padding-top: .75rem;
            padding-bottom: .75rem;
            font-size: 1rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            padding: 3px 5px;
            border-radius: 50%;
            font-size: 0.5rem;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="/">Bidding</a>
        <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap dropdown">
                <a class="nav-link px-3 dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo Auth::user()->name; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="/perfil">Meu Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="/logout" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item">Sair</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('/') ? 'active' : ''; ?>" href="/">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('licitacoes*') ? 'active' : ''; ?>" href="/licitacoes">
                                <i class="fas fa-file-contract"></i> Licitações
                                <?php
                                $licitacoesProximas = App\Models\Licitacao::where('data_encerramento_proposta', '>=', Carbon\Carbon::now())
                                                    ->where('data_encerramento_proposta', '<=', Carbon\Carbon::now()->addDays(3))
                                                    ->where('interesse', true)
                                                    ->count();
                                if ($licitacoesProximas > 0):
                                ?>
                                <span class="badge bg-danger notification-badge"><?php echo $licitacoesProximas; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('propostas*') ? 'active' : ''; ?>" href="/propostas">
                                <i class="fas fa-file-signature"></i> Propostas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('clientes*') ? 'active' : ''; ?>" href="/clientes">
                                <i class="fas fa-users"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('relatorios*') ? 'active' : ''; ?>" href="/relatorios">
                                <i class="fas fa-chart-bar"></i> Relatórios
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Configurações</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('usuarios*') ? 'active' : ''; ?>" href="/usuarios">
                                <i class="fas fa-users-cog"></i> Usuários
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo request()->is('configuracoes*') ? 'active' : ''; ?>" href="/configuracoes">
                                <i class="fas fa-cog"></i> Configurações
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Mensagens flash -->
                <?php if (session()->has('success')): ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <?php echo session('success'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php endif; ?>

                <?php if (session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?php echo session('error'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <?php endif; ?>

                <!-- Conteúdo da página será inserido aqui -->
                <?php echo $content ?? ''; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Scripts CSRF para AJAX -->
    <script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
        }
    });
    </script>

    <!-- Scripts específicos da página serão inseridos aqui -->
    <?php echo $scripts ?? ''; ?>
</body>
</html>
