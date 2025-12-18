<?php
define('ACCESO_PERMITIDO', true); 
include 'config.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (defined('ADMIN_USER') && defined('ADMIN_PASS') && $usuario === ADMIN_USER && $password === ADMIN_PASS) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['admin_logueado'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Viviana Woods</title> 
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('crossfit.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            font-family: 'Roboto', sans-serif;
        }

        .login-container {
            background: #1e1e1e;
            padding: 40px;
            border-radius: 8px;
            border-top: 5px solid #FFD700; 
            width: 90%; 
            max-width: 450px;          
            box-shadow: 0 15px 25px rgba(0,0,0,0.7);
            text-align: center;
            box-sizing: border-box; 
        }

        .login-container h2 {
            color: #FFD700;
            margin-top: 0;
            margin-bottom: 30px;
            font-size: 2rem;
            font-family: 'Oswald', sans-serif; 
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            color: #bbb;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            background: #2b2b2b;
            border: 1px solid #444;
            color: white;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif; 
            font-size: 1rem;
        }

        .input-group input:focus {
            border-color: #FFD700;
            outline: none;
            background: #333;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            margin-top: 10px;
            background: #FFD700;
            border: none;
            color: black;
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
            cursor: pointer;
            font-family: 'Oswald', sans-serif; 
            transition: 0.3s;
            border-radius: 4px;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            background: #e6c200;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(255, 215, 0, 0.2);
        }

        .error-msg {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid #dc3545;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 0.95rem;
        }

        .back-link {
            display: block;
            margin-top: 25px;
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-link:hover { color: #FFD700; }

        /* --- MEDIA QUERY PARA MÓVILES --- */
        @media (max-width: 480px) {
            .login-container {
                padding: 25px 20px; 
                margin-top: -20px; 
            }

            .login-container h2 {
                font-size: 1.6rem; 
                margin-bottom: 20px;
            }

            .btn-login {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Acceso Admin</h2>
        
        <?php if ($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label><i class="fas fa-user"></i> Usuario</label>
                <input type="text" name="usuario" required autofocus placeholder="Ingresa tu usuario">
            </div>
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" name="password" required placeholder="Ingresa tu contraseña">
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        
        <a href="index.html" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al sitio
        </a>
    </div>
</body>
</html>