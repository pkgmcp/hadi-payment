<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .detail-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .status-success { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .status-pending { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .status-failed { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
        .status-refunded { background: linear-gradient(135deg, #6f42c1, #6610f2); color: white; }
        .status-default { background: #6c757d; color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card detail-card mt-5">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-2">
                            <i class="fas fa-receipt me-2"></i>
                            Payment Details
                        </h3>
                        <p class="text-muted text-center mb-4">Transaction ID: {{ $payment->transaction_id ?? 'N/A' }}</p>

                        <div class="text-center mb-4">
                            @php
                                $statusClass = match (($payment->status ?? '')) {
                                    'completed', 'success', 'successful' => 'status-success',
                                    'pending', 'processing' => 'status-pending',
                                    'failed', 'cancelled', 'canceled' => 'status-failed',
                                    'refunded' => 'status-refunded',
                                    default => 'status-default',
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                <i class="fas fa-credit-card me-2"></i>
                                {{ ucfirst($payment->status ?? 'unknown') }}
                            </span>
                        </div>

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <div class="row text-start">
                                    <div class="col-6 py-1"><strong>Order ID:</strong></div>
                                    <div class="col-6 py-1"><code>{{ $payment->order_id ?? 'N/A' }}</code></div>
                                    <div class="col-6 py-1"><strong>Gateway:</strong></div>
                                    <div class="col-6 py-1">{{ ucfirst($payment->gateway ?? 'N/A') }}</div>
                                    <div class="col-6 py-1"><strong>Amount:</strong></div>
                                    <div class="col-6 py-1">
                                        <strong>{{ number_format((float) ($payment->amount ?? 0), 2) }} {{ $payment->currency ?? 'BDT' }}</strong>
                                    </div>
                                    <div class="col-6 py-1"><strong>Date:</strong></div>
                                    <div class="col-6 py-1">
                                        {{ $payment->created_at ? $payment->created_at->format('M d, Y H:i:s') : 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>
                                Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
