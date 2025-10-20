<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicativo Balé</title>


    <link rel="stylesheet" href="https://unpkg.com/marx-css/css/marx.min.css">

    <script src="includes\js\scripts.js"></script>

</head>

<body>
    <header>
        <div style="place-items:center;display:grid;">
            <a href="/agenda">
                <img src="includes\img\logo.jpg" alt="Logo da Aplicação" width="300px">
            </a>
            <nav>
                <ul>
                    <li><a href="/agenda">Início</a></li>

                    <?php

                    if (!isset($_SESSION['user_id'])): ?>

                        <li><a href="?route=login">Login</a></li>

                    <?php endif; ?>


                    <?php

                    if (isset($_SESSION['user_id'])): ?>

                        <li><a href="?route=empresa">Empresas</a></li>
                        <li><a href="?route=login&action=logout">Logout</a></li>

                    <?php endif; ?>

                </ul>
            </nav>
        </div>
    </header>

    <main>