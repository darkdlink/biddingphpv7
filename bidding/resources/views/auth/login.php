<?php
session_start();

// Verificar se já está logado
if (isset($_SESSION['user'])) {
    header('Location: /dashboard');
    exit;
}

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validação básica
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Aqui você implementaria a autenticação real
        // Este é apenas um exemplo simplificado
        try {
            $user = \App\Models\User::where('email', $email)->first();

            if ($user && password_verify($password, $user->password)) {
                // Login bem-sucedido
                $_SESSION['user'] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                // Redirecionar para o dashboard
                header('Location: /dashboard');
                exit;
            } else {
                $error = 'Email ou senha incorretos.';
            }
        } catch (\Exception $e) {
            $error = 'Erro ao processar o login. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Capitalização de Licitações</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background-color: #2c3e50;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
            text-align: center;
        }

        .card-header i {
            font-size: 3rem;
            margin-bottom: 10px;
            color: #1abc9c;
        }

        .form-control {
            padding: 12px;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #1abc9c;
            border-color: #1abc9c;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #16a085;
            border-color: #16a085;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-gavel"></i>
                <h3>Bidding</h3>
                <p>Sistema de Capitalização de Licitações</p>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="/login">
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Lembrar-me</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
