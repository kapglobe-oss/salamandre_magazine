<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur - Salamandre</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0c20 0%, #15102a 50%, #241435 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            padding: 1rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 3.5rem 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            text-align: center;
        }

        .login-logo svg {
            width: 50px;
            height: 50px;
            fill: var(--accent-gold);
            margin-bottom: 1rem;
        }

        .login-title {
            font-family: var(--font-display);
            font-size: 1.6rem;
            letter-spacing: 0.05em;
            margin-bottom: 2.5rem;
            color: #fff;
        }

        .form-group {
            margin-bottom: 1.8rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.6);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
            color: #fff;
            outline: none;
            border-radius: 4px;
            font-family: var(--font-sans);
            transition: var(--transition-fast);
        }

        .form-control:focus {
            border-color: var(--accent-gold);
            background: rgba(0,0,0,0.4);
        }

        .error-message {
            color: #e35461;
            font-size: 0.85rem;
            margin-top: 1rem;
            display: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-logo">
            <svg viewBox="0 0 100 100">
                <path d="M50 15C42 15 35 22 35 30C35 38 41 40 45 42C48 43 51 45 51 48C51 51 47 54 42 54C35 54 30 48 27 44C25 41 21 38 17 40C13 42 12 47 15 51C20 58 29 65 40 65C52 65 62 57 62 46C62 36 54 33 49 31C45 29 43 27 43 24C43 21 47 19 51 19C57 19 63 24 66 28C68 31 73 33 77 30C81 27 80 21 76 17C69 11 60 15 50 15ZM30 75C28 75 26 77 26 79C26 81 28 83 30 83C32 83 34 81 34 79C34 77 32 75 30 75ZM70 75C68 75 66 77 66 79C66 81 68 83 70 83C72 83 74 81 74 79C74 77 72 75 70 75Z"/>
            </svg>
        </div>
        <h2 class="login-title">Administrateur</h2>
        
        <form id="login-form">
            <div class="form-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" class="form-control" placeholder="Entrez votre identifiant" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="off">
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; justify-content: center; margin-top: 1rem; border-radius: 4px;">Se connecter</button>
            <div id="error-box" class="error-message"></div>
        </form>
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const user = document.getElementById('username').value;
        const pass = document.getElementById('password').value;
        const errorBox = document.getElementById('error-box');
        
        errorBox.style.display = 'none';

        fetch('../api.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: user, password: pass })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw new Error(err.error || 'Erreur inconnue') });
            }
            return res.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = 'index.php';
            }
        })
        .catch(err => {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        });
    });
    </script>
</body>
</html>
