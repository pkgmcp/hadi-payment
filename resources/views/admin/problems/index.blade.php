@extends('hadi-payment::admin.layout')

@section('title', 'Payment Problems')

@section('content')
    <div class="card stat-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payment.problems') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach (['open', 'in_progress', 'pending_customer', 'pending_gateway', 'resolved', 'closed'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Severity</label>
                    <select name="severity" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach (['low', 'medium', 'high', 'critical'] as $severity)
                            <option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                            <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control form-control-sm" placeholder="Title / description">
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
                            <th>Title</th>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($problems as $problem)
                            <tr>
                                <td>{{ $problem->id }}</td>
                                <td>{{ $problem->title }}</td>
                                <td>{{ $problem->problem_type }}</td>
                                <td>
                                    <span class="badge bg-{{ $problem->severity === 'critical' ? 'danger' : ($problem->severity === 'high' ? 'warning' : 'secondary') }} text-dark">
                                        {{ ucfirst($problem->severity) }}
                                    </span>
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $problem->status)) }}</td>
                                <td>{{ ucfirst($problem->priority) }}</td>
                                <td>{{ $problem->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.payment.problems.show', $problem) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No problems found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-body d-flex justify-content-center">
            {{ $problems->links() }}
        </div>
    </div>
@endsection
