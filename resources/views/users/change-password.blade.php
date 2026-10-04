@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update-password') }}">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">New Password *</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                            <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Confirm New Password *</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save mr-2"></i>Change Password
                            </button>
                            <a href="{{ route('users.profile') }}" class="btn btn-secondary btn-lg">
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
