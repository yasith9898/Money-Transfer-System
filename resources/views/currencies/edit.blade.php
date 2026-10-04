@extends('layouts.app')

@section('title', 'Edit Currency')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>Edit Currency
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('currencies.update', $currency->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Currency Code *</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code', $currency->code) }}" required maxlength="3">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Currency Name *</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $currency->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Symbol *</label>
                            <input type="text" name="symbol" class="form-control"
                                   value="{{ old('symbol', $currency->symbol) }}" required maxlength="10">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Decimal Places *</label>
                            <select name="decimal_places" class="form-control" required>
                                @foreach ([0,2,3,4] as $places)
                                    <option value="{{ $places }}" {{ old('decimal_places', $currency->decimal_places) == $places ? 'selected' : '' }}>
                                        {{ $places }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                            <a href="{{ route('currencies.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left mr-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
