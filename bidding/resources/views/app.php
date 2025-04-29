<?php
// Iniciar a sessão
session_start();
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Capitalização de Licitações</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #1abc9c;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .navbar-brand i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border: none;
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px 8px 0 0 !important;
            padding: 15px;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: #16a085;
            border-color: #16a085;
        }

        .sidebar {
            height: 100%;
            position: fixed;
            top: 56px;
            left: 0;
            width: 250px;
            background-color: white;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 100;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
        }

        .nav-link {
            color: var(--dark-gray);
            padding: 10px 20px;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: var(--light-gray);
            border-left: 3px solid var(--secondary-color);
        }

        .nav-link.active {
            border-left: 3px solid var(--secondary-color);
            background-color: var(--light-gray);
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 10px;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-open {
            background-color: #28a745;
            color: white;
        }

        .status-closed {
            background-color: #dc3545;
            color: white;
        }

        .status-draft {
            background-color: #ffc107;
            color: black;
        }

        .dashboard-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .dashboard-card i {
            font-size: 3rem;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/"><i class="fas fa-gavel"></i> Bidding</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-id-card"></i> Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="/logout" method="POST" id="logout-form">
                                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Sair</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/login"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($user): ?>
        <div class="sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/biddings">
                        <i class="fas fa-list-alt"></i> Licitações
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/proposals">
                        <i class="fas fa-file-contract"></i> Propostas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/entities">
                        <i class="fas fa-building"></i> Entidades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/reports">
                        <i class="fas fa-chart-line"></i> Relatórios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/settings">
                        <i class="fas fa-cogs"></i> Configurações
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <div class="<?php echo $user ? 'content' : 'container mt-4'; ?>">
        <div id="app">
            <!-- Conteúdo será carregado aqui via JavaScript -->
        </div>
    </div>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <!-- SPA Router Script -->
    <script>
        // Token CSRF para requisições AJAX
        const csrfToken = "<?php echo csrf_token(); ?>";

        // Config para requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // Router simples para SPA
        const router = {
            routes: {
                '/': 'dashboard',
                '/dashboard': 'dashboard',
                '/biddings': 'biddings',
                '/biddings/new': 'biddingForm',
                '/biddings/edit/:id': 'biddingForm',
                '/biddings/:id': 'biddingDetail',
                '/proposals': 'proposals',
                '/proposals/new': 'proposalForm',
                '/proposals/edit/:id': 'proposalForm',
                '/proposals/:id': 'proposalDetail',
                '/entities': 'entities',
                '/entities/new': 'entityForm',
                '/entities/edit/:id': 'entityForm',
                '/entities/:id': 'entityDetail',
                '/reports': 'reports',
                '/settings': 'settings',
                '/login': 'login',
                '/profile': 'profile'
            },

            init: function() {
                this.loadRoute();

                // Interceptar cliques em links
                $(document).on('click', 'a', function(e) {
                    const href = $(this).attr('href');
                    if (href && href.startsWith('/') && !href.startsWith('//')) {
                        e.preventDefault();
                        history.pushState(null, null, href);
                        router.loadRoute();
                    }
                });

                // Handle navegação do browser
                window.addEventListener('popstate', () => this.loadRoute());
            },

            loadRoute: function() {
                const path = window.location.pathname;
                let route = null;
                let params = {};

                // Encontrar rota correspondente
                for (const [pattern, view] of Object.entries(this.routes)) {
                    const match = this.matchRoute(path, pattern);
                    if (match) {
                        route = view;
                        params = match;
                        break;
                    }
                }

                if (route) {
                    this.loadView(route, params);
                } else {
                    this.loadView('notFound');
                }
            },

            matchRoute: function(path, pattern) {
                const patternParts = pattern.split('/');
                const pathParts = path.split('/');

                if (patternParts.length !== pathParts.length) {
                    return null;
                }

                const params = {};

                for (let i = 0; i < patternParts.length; i++) {
                    const patternPart = patternParts[i];
                    const pathPart = pathParts[i];

                    if (patternPart.startsWith(':')) {
                        // Extrai parâmetro
                        const paramName = patternPart.substring(1);
                        params[paramName] = pathPart;
                    } else if (patternPart !== pathPart) {
                        return null; // Não corresponde
                    }
                }

                return params;
            },

            loadView: function(view, params = {}) {
                // Atualizar navegação
                $('.nav-link').removeClass('active');
                $(`.nav-link[href^="/${view.split('/')[0]}"]`).addClass('active');

                // Carregar conteúdo da view
                $.ajax({
                    url: `/views/${view}.php`,
                    type: 'GET',
                    data: params,
                    success: function(data) {
                        $('#app').html(data);

                        // Se existe uma função de inicialização para esta view
                        if (typeof window[`init${view.charAt(0).toUpperCase() + view.slice(1)}`] === 'function') {
                            window[`init${view.charAt(0).toUpperCase() + view.slice(1)}`](params);
                        }
                    },
                    error: function() {
                        $('#app').html('<div class="alert alert-danger">Erro ao carregar a página.</div>');
                    }
                });
            }
        };

        // Iniciar o router quando o documento estiver pronto
        $(document).ready(function() {
            router.init();
        });
    </script>
</body>
</html>
