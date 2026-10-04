@extends('layouts.app')

@section('title', 'Currencies')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Currencies</h1>
        <a href="{{ route('currencies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Currency
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Decimal Places</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($currencies as $currency)
                        <tr>
                            <td>
                                <strong>{{ $currency->code }}</strong>
                                @if($currency->isBaseCurrency())
                                    <span class="badge badge-primary ml-1">Base</span>
                                @endif
                            </td>
                            <td>{{ $currency->name }}</td>
                            <td>{{ $currency->symbol }}</td>
                            <td>{{ $currency->decimal_places }}</td>
                            <td>
                                @if($currency->is_active)
                                    <span class="badge badge-success" style="background-color: #1cc88a; padding: 0.5em 0.75em;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="background-color: #e74a3b; padding: 0.5em 0.75em;">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('currencies.show', $currency->id) }}"
                                       class="btn btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('currencies.edit', $currency->id) }}"
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('currencies.toggle-status', $currency->id) }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-{{ $currency->is_active ? 'secondary' : 'success' }}"
                                                title="{{ $currency->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $currency->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                        </button>
                                    </form>
                                    @if($currency->canBeDeleted())
                                    <form action="{{ route('currencies.destroy', $currency->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this currency?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-coins fa-3x mb-3"></i>
                                <p>No currencies found. <a href="{{ route('currencies.create') }}">Create the first currency</a></p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($currencies->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $currencies->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
