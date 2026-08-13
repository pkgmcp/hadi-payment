@extends('hadi-payment::admin.layout')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Invoice <code>{{ $invoice->invoice_number }}</code></h5>
                            <div class="text-muted small">
                                Issued {{ $invoice->invoice_date?->format('Y-m-d') }} &middot; Due {{ $invoice->due_date?->format('Y-m-d') }}
                            </div>
                        </div>
                        <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning') }} text-dark badge-soft">
                            {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                        </span>
                    </div>

                    <table class="table table-sm">
                        <tr><th class="w-25 text-muted">Customer</th><td>{{ $invoice->user->name ?? 'N/A' }} &lt;{{ $invoice->user->email ?? '' }}&gt;</td></tr>
                        <tr><th class="text-muted">Payment</th><td>
                            @if ($invoice->payment)
                                <a href="{{ route('admin.payment.show', $invoice->payment) }}">#{{ $invoice->payment->id }} ({{ $invoice->payment->gateway }})</a>
                            @else
                                N/A
                            @endif
                        </td></tr>
                        <tr><th class="text-muted">Subtotal</th><td>{{ number_format($invoice->subtotal, 2) }}</td></tr>
                        <tr><th class="text-muted">Tax</th><td>{{ number_format($invoice->tax_amount, 2) }}</td></tr>
                        <tr><th class="text-muted">Discount</th><td>{{ number_format($invoice->discount_amount, 2) }}</td></tr>
                        <tr><th class="text-muted">Total</th><td><strong>{{ number_format($invoice->total_amount, 2) }} {{ $invoice->currency }}</strong></td></tr>
                        <tr><th class="text-muted">Notes</th><td>{{ $invoice->notes ?? 'N/A' }}</td></tr>
                    </table>

                    <h6 class="mt-4 mb-2"><i class="fas fa-box me-2"></i>Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->description ?? $item->name }}</td>
                                        <td class="text-end">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No items.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-tools me-2"></i>Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.payment.invoices.download', $invoice) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Download PDF
                        </a>
                        <button class="btn btn-success btn-sm" data-send-invoice="{{ route('admin.payment.invoices.send', $invoice) }}">
                            <i class="fas fa-envelope me-1"></i>Send Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-send-invoice]').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Send this invoice to the customer?')) return;
        const res = await fetch(btn.dataset.sendInvoice, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const data = await res.json();
        alert(data.message);
    });
});
</script>
@endpush
