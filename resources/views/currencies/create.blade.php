@extends('layouts.app')

@section('title', 'Create Currency')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle mr-2"></i>Create New Currency
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('currencies.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Currency Code *</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code') }}" required maxlength="3"
                                   placeholder="e.g., USD, EUR, IQD">
                            <small class="form-text text-muted">3-letter currency code (ISO 4217)</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Currency Name *</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name') }}" required
                                   placeholder="e.g., US Dollar, Euro, Iraqi Dinar">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Symbol *</label>
                            <input type="text" name="symbol" class="form-control"
                                   value="{{ old('symbol') }}" required maxlength="10"
                                   placeholder="e.g., $, €, د.ع">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Decimal Places *</label>
                            <select name="decimal_places" class="form-control" required>
                                <option value="0" {{ old('decimal_places') == 0 ? 'selected' : '' }}>0 (No decimals)</option>
                                <option value="2" {{ old('decimal_places') == 2 ? 'selected' : '' }} selected>2 (Standard)</option>
                                <option value="3" {{ old('decimal_places') == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ old('decimal_places') == 4 ? 'selected' : '' }}>4</option>
                            </select>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Create Currency
                            </button>
                            <a href="{{ route('currencies.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
