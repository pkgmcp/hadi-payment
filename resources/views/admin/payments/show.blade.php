@extends('hadi-payment::admin.layout')

@section('title', 'Payment #' . $payment->id)

@section('content')
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Payment #{{ $payment->id }}</h5>
                            <div class="text-muted small">
                                {{ $payment->gateway }} &middot; {{ $payment->currency }}
                            </div>
                        </div>
                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }} text-dark badge-soft">
                            {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                        </span>
                    </div>

                    <table class="table table-sm">
                        <tr><th class="w-25 text-muted">Order ID</th><td><code>{{ $payment->order_id }}</code></td></tr>
                        <tr><th class="text-muted">Reference ID</th><td><code>{{ $payment->reference_id }}</code></td></tr>
                        <tr><th class="text-muted">Transaction ID</th><td><code>{{ $payment->transaction_id }}</code></td></tr>
                        <tr><th class="text-muted">Amount</th><td>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td></tr>
                        <tr><th class="text-muted">Refunded Amount</th><td>{{ number_format($payment->refunded_amount, 2) }}</td></tr>
                        <tr><th class="text-muted">Product</th><td>{{ $payment->product_name }}</td></tr>
                        <tr><th class="text-muted">Customer</th><td>{{ $payment->user->name ?? 'N/A' }} &lt;{{ $payment->user->email ?? '' }}&gt;</td></tr>
                        <tr><th class="text-muted">Created</th><td>{{ $payment->created_at?->format('Y-m-d H:i:s') }}</td></tr>
                        <tr><th class="text-muted">Processed</th><td>{{ $payment->processed_at?->format('Y-m-d H:i:s') }}</td></tr>
                        <tr><th class="text-muted">Expires</th><td>{{ $payment->expires_at?->format('Y-m-d H:i:s') }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>History</h6>
                </div>
                <div class="card-body">
                    @forelse ($payment->histories as $history)
                        <div class="d-flex border-bottom py-2">
                            <div class="me-3">
                                <span class="badge bg-light text-dark">{{ $history->created_at?->format('H:i') }}</span>
                            </div>
                            <div>
                                <div>{{ $history->action ?? 'update' }}</div>
                                <div class="small text-muted">
                                    @if ($history->old_value)
                                        {{ $history->old_value }} &rarr;
                                    @endif
                                    {{ $history->new_value }}
                                    @if ($history->description)
                                        &middot; {{ $history->description }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No history recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card stat-card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Update Status</h6>
                </div>
                <div class="card-body">
                    <form data-action="status">
                        <div class="mb-2">
                            <select name="status" class="form-select form-select-sm">
                                @foreach (['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded'] as $status)
                                    <option value="{{ $status }}" @selected($payment->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Admin notes (optional)"></textarea>
                        </div>
                        <button class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Save Status
                        </button>
                    </form>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Problems</h6>
                </div>
                <div class="card-body">
                    @forelse ($payment->problems as $problem)
                        <a href="{{ route('admin.payment.problems.show', $problem) }}" class="text-decoration-none">
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ $problem->title }}</strong>
                                    <span class="badge bg-warning text-dark">{{ $problem->severity }}</span>
                                </div>
                                <div class="small text-muted">{{ $problem->status }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-muted mb-0">No problems reported.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('form[data-action="status"]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('{{ route('admin.payment.update-status', $payment) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(new FormData(form))),
        });
        const data = await res.json();
        alert(data.message);
        if (data.success) location.reload();
    });
});
</script>
@endpush
