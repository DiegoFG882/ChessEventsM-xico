<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$tituloPagina =$tituloPagina ?? 'ChessEvents México - Cartelera Nacional de Ajedrez';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <link rel="icon" type="image/png" href="log.png">
    
    <!-- Bootstrap 5.3 CSS + Bootstrap Icons (Iconos vectoriales SVG) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --chess-dark: #0f172a;
            --chess-primary: #3730a3;
            --chess-accent: #f59e0b;
            --chess-bg: #f8fafc;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--chess-bg);
            color: #1e293b;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        .navbar-chess {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .btn-chess-primary {
            background-color: var(--chess-primary);
            border-color: var(--chess-primary);
            color: #ffffff;
            font-weight: 700;
        }
        .btn-chess-primary:hover {
            background-color: #312e81;
            border-color: #312e81;
            color: #ffffff;
        }
        .card-torneo {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-torneo:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        }
        .badge-fide {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
    </style>
</head>
<body>