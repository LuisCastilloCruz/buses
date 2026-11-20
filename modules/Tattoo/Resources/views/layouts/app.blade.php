<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modulo Tattoo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8fafc;
            font-family: Arial, sans-serif;
        }
        .tattoo-sidebar {
            min-height: 100vh;
            background: #111827;
            color: #fff;
            padding: 1.5rem 1rem;
        }
        .tattoo-sidebar h4 {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .tattoo-sidebar a {
            display: block;
            color: #d1d5db;
            padding: .65rem .75rem;
            border-radius: 10px;
            text-decoration: none;
            margin-bottom: .35rem;
        }
        .tattoo-sidebar a:hover,
        .tattoo-sidebar a.active {
            background: #1f2937;
            color: #fff;
        }
        .tattoo-content {
            padding: 2rem;
        }
        .card {
            border: 0;
            border-radius: 16px;
        }
        .shadow-soft {
            box-shadow: 0 0.5rem 1.25rem rgba(15, 23, 42, 0.08);
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row no-gutters">
        <div class="col-md-2 tattoo-sidebar">
            <h4>Tattoo</h4>
            <a href="/tattoo-dashboard" class="active">Dashboard</a>
            <a href="/tattoo-appointments">Agenda</a>
            <a href="/tattoo-artists">Artistas</a>
            <a href="/tattoo-styles">Estilos</a>
            <a href="/tattoo-sessions">Sesiones</a>
            <a href="/tattoo-consents">Consentimientos</a>
        </div>
        <div class="col-md-10 tattoo-content">
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>