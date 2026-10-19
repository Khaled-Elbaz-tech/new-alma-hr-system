@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-body">
                <h5>Welcome, {{ $user->name }}!</h5>
                <p class="text-muted">Role: {{ $user->role->display_name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

@if($user->isEmployee() && !$user->isHrManager() && !$user->isManager() && !$user->isDM() && !$user->isSuperAdmin())
    @include('dashboard.employee-view')
@elseif($user->isHrManager() || $user->isSuperAdmin())
    @include('dashboard.hr-view')
@elseif($user->isManager() || $user->isDM())
    @include('dashboard.manager-view')
@else
    @include('dashboard.employee-view')
@endif
@endsection