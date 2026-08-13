@extends('hadi-payment::admin.layout')

@section('title', 'Payment Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary me-3">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Payments</div>
                        <div class="fs-4 fw-bold">{{ number_format($paymentStats['total_payments'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success me-3">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Amount</div>
                        <div class="fs-4 fw-bold">{{ number_format($paymentStats['total_amount'] ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger me-3">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Open Problems</div>
                        <div class="fs-4 fw-bold">{{ number_format($problemStats['open_problems'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="fas fa-check-circle me-2 text-success"></i>Payment Summary</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Completed</span>
                            <span class="badge bg-success">{{ number_format($paymentStats['completed_payments'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Pending</span>
                            <span class="badge bg-warning text-dark">{{ number_format($paymentStats['pending_payments'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Failed</span>
                            <span class="badge bg-danger">{{ number_format($paymentStats['failed_payments'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Refunded</span>
                            <span class="badge bg-secondary">{{ number_format($paymentStats['refunded_payments'] ?? 0) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Problem Summary</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Total</span>
                            <span class="badge bg-secondary">{{ number_format($problemStats['total_problems'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Open</span>
                            <span class="badge bg-warning text-dark">{{ number_format($problemStats['open_problems'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Resolved</span>
                            <span class="badge bg-success">{{ number_format($problemStats['resolved_problems'] ?? 0) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="fas fa-file-invoice me-2 text-primary"></i>Invoice Summary</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Total</span>
                            <span class="badge bg-secondary">{{ number_format($invoiceStats['total_invoices'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Paid</span>
                            <span class="badge bg-success">{{ number_format($invoiceStats['paid_invoices'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Overdue</span>
                            <span class="badge bg-danger">{{ number_format($invoiceStats['overdue_invoices'] ?? 0) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span class="text-muted">Paid Amount</span>
                            <span class="fw-bold">{{ number_format($invoiceStats['paid_amount'] ?? 0, 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
