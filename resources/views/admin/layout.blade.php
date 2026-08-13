<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Payment Admin') - Hadi Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: linear-gradient(180deg, #1a1a2e, #16213e);
            min-height: 100vh;
            color: #fff;
        }
        .sidebar .nav-link {
            color: #aab;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 2px 8px;
            transition: all .2s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, .1);
            color: #fff;
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .06);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
        }
        .badge-soft {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0">
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="p-3 fs-5 fw-bold">
                <i class="fas fa-credit-card me-2"></i>Hadi Payment
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="{{ route('admin.payment.dashboard') }}">
                    <i class="fas fa-chart-line"></i>Dashboard
                </a>
                <a class="nav-link" href="{{ route('admin.payment.index') }}">
                    <i class="fas fa-list"></i>Payments
                </a>
                <a class="nav-link" href="{{ route('admin.payment.problems') }}">
                    <i class="fas fa-exclamation-triangle"></i>Problems
                </a>
                <a class="nav-link" href="{{ route('admin.payment.invoices') }}">
                    <i class="fas fa-file-invoice"></i>Invoices
                </a>
            </nav>
        </div>
        <div class="col-md-10">
            <nav class="navbar navbar-light bg-white shadow-sm px-4">
                <span class="navbar-brand mb-0 h6">
                    @yield('title', 'Payment Admin')
                </span>
                <a href="{{ route('admin.payment.export') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-download me-1"></i>Export CSV
                </a>
            </nav>
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
