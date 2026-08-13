@extends('hadi-payment::admin.layout')

@section('title', 'Invoices')

@section('content')
    <div class="card stat-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payment.invoices') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach (['draft', 'sent', 'paid', 'partially_paid', 'overdue', 'cancelled'] as $status)
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
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Invoice number / order ID">
                </div>
                <div class="col-md-2 d-grid">
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
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Due</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td><code>{{ $invoice->invoice_number }}</code></td>
                                <td>{{ $invoice->user->name ?? 'N/A' }}</td>
                                <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                                <td>{{ $invoice->due_date?->format('Y-m-d') }}</td>
                                <td>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }} text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.payment.invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body d-flex justify-content-center">
            {{ $invoices->links() }}
        </div>
    </div>
@endsection
