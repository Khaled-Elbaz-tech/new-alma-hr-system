@extends('layouts.app')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Change Your Password</h5>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                
                <form method="POST" action="{{ route('password.change') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-lock"></i> Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" class="form-control" required>
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-plus-circle"></i> New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" class="form-control" 
                                   required minlength="8" 
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$"
                                   title="Must contain: uppercase, lowercase, number, special character (@$!%*?&)">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <small class="text-muted">Minimum 8 characters. Must include: uppercase, lowercase, number, special character (@$!%*?&)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection