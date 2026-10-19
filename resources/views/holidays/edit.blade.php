@extends('layouts.app')

@section('title', 'Edit Holiday')
@section('page-title', 'Edit Holiday')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Holiday</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('holidays.update', $holiday) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $holiday->name) }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $holiday->start_date->format('Y-m-d')) }}" required>
                            @error('start_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $holiday->end_date->format('Y-m-d')) }}" required>
                            @error('end_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Year <span class="text-danger">*</span></label>
                        <select name="year" class="form-select" required>
                            @for($y = date('Y') - 1; $y <= date('Y') + 5; $y++)
                                <option value="{{ $y }}" {{ old('year', $holiday->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('year')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_recurring" class="form-check-input" id="isRecurring" value="1" {{ $holiday->is_recurring ? 'checked' : '' }}>
                            <label class="form-check-label" for="isRecurring">
                                <strong>Recurring Holiday</strong>
                                <br>
                                <small class="text-muted">This holiday repeats every year</small>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $holiday->description) }}</textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Holiday
                    </button>
                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection