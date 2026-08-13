@extends('hadi-payment::admin.layout')

@section('title', 'Problem #' . $problem->id)

@section('content')
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card stat-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Problem #{{ $problem->id }}: {{ $problem->title }}</h5>
                            <div class="text-muted small">
                                {{ $problem->problem_type }} &middot; reported by {{ $problem->reportedBy->name ?? 'System' }}
                            </div>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark">{{ ucfirst($problem->severity) }}</span>
                            <span class="badge bg-secondary">{{ ucfirst($problem->priority) }}</span>
                        </div>
                    </div>

                    <table class="table table-sm">
                        <tr><th class="w-25 text-muted">Payment</th><td>
                            @if ($problem->payment)
                                <a href="{{ route('admin.payment.show', $problem->payment) }}">#{{ $problem->payment->id }} ({{ $problem->payment->gateway }})</a>
                            @else
                                N/A
                            @endif
                        </td></tr>
                        <tr><th class="text-muted">Status</th><td>{{ ucfirst(str_replace('_', ' ', $problem->status)) }}</td></tr>
                        <tr><th class="text-muted">Assigned To</th><td>{{ $problem->assignedTo->name ?? 'Unassigned' }}</td></tr>
                        <tr><th class="text-muted">Created</th><td>{{ $problem->created_at?->format('Y-m-d H:i:s') }}</td></tr>
                        <tr><th class="text-muted">Resolved</th><td>{{ $problem->resolved_at?->format('Y-m-d H:i:s') }}</td></tr>
                        <tr><th class="text-muted">Resolution Notes</th><td>{{ $problem->resolution_notes ?? 'N/A' }}</td></tr>
                    </table>

                    <div class="alert alert-light border">
                        <h6 class="fw-bold">Description</h6>
                        <p class="mb-0">{{ $problem->description }}</p>
                    </div>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Comments</h6>
                </div>
                <div class="card-body">
                    @forelse ($problem->comments as $comment)
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->user->name ?? 'Admin' }}</strong>
                                <small class="text-muted">{{ $comment->created_at?->format('Y-m-d H:i') }}</small>
                            </div>
                            <div class="small">{{ $comment->comment }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No comments yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card stat-card mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-user-check me-2"></i>Assign</h6>
                </div>
                <div class="card-body">
                    <form data-action="assign">
                        <div class="mb-2">
                            <input type="number" name="assigned_to" class="form-control form-control-sm" placeholder="Admin user ID">
                        </div>
                        <button class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-check me-1"></i>Assign
                        </button>
                    </form>
                </div>
            </div>

            <div class="card stat-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Resolve</h6>
                </div>
                <div class="card-body">
                    <form data-action="resolve">
                        <div class="mb-2">
                            <textarea name="resolution_notes" class="form-control form-control-sm" rows="3" placeholder="Resolution notes"></textarea>
                        </div>
                        <button class="btn btn-success btn-sm w-100">
                            <i class="fas fa-check me-1"></i>Resolve Problem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('form[data-action]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const action = form.dataset.action;
        const url = action === 'assign'
            ? '{{ route('admin.payment.problems.assign', $problem) }}'
            : '{{ route('admin.payment.problems.resolve', $problem) }}';
        const res = await fetch(url, {
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
