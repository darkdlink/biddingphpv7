<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bidding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html, body {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
        }

        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
</head>
<body class="text-center">
    <main class="form-signin">
        <form action="/login" method="POST">
            <?php echo csrf_field(); ?>

            <h1 class="h3 mb-3 fw-normal">Bidding</h1>
            <h2 class="h5 mb-3 fw-normal">Sistema de Capitalização de Licitações</h2>

            <?php if (isset($errors) && $errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors->all() as $error): ?>
                    <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" value="<?php echo old('email', ''); ?>">
                <label for="email">Email</label>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Senha">
                <label for="password">Senha</label>
            </div>

            <div class="checkbox mb-3 text-start">
                <label>
                    <input type="checkbox" name="remember"> Lembrar de mim
                </label>
            </div>

            <button class="w-100 btn btn-lg btn-primary" type="submit">Entrar</button>

            <p class="mt-5 mb-3 text-muted">&copy; 2024 Bidding</p>
        </form>
    </main>
</body>
</html>
