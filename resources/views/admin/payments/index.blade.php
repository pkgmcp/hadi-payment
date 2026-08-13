@extends('hadi-payment::admin.layout')

@section('title', 'Payments')

@section('content')
    <div class="card stat-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payment.index') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Gateway</label>
                    <input type="text" name="gateway" value="{{ $filters['gateway'] ?? '' }}" class="form-control form-control-sm" placeholder="e.g. bkash">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach (['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Order / Payment / Transaction ID">
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Order ID</th>
                            <th>Gateway</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td>{{ $payment->id }}</td>
                                <td><code>{{ $payment->order_id }}</code></td>
                                <td>{{ $payment->gateway }}</td>
                                <td>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }} text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                                    </span>
                                </td>
                                <td>{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.payment.show', $payment) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body d-flex justify-content-center">
            {{ $payments->links() }}
        </div>
    </div>
@endsection
