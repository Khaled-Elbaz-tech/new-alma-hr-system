@extends('layouts.app')

@section('title', 'Edit Field')
@section('page-title', 'Edit Custom Field')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Field: {{ $employeeField->field_label }}</h5>
            </div>
            <div class="card-body">
                  <form method="POST" action="{{ route('dynamic-forms.update', $employeeField->id) }}">
                    @csrf
                    @method('PUT')
                    <!-- DEBUG: Form action uses explicit parameter mapping -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Field Label</label>
                            <input type="text" name="field_label" class="form-control" value="{{ $employeeField->field_label }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" name="field_section" class="form-control" value="{{ $employeeField->field_section }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Options (JSON format)</label>
                        <textarea name="field_options" class="form-control" rows="2">{{ $employeeField->field_options ? json_encode($employeeField->field_options) : '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="field_order" class="form-control" value="{{ $employeeField->field_order }}">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_required" class="form-check-input" id="is_required" {{ $employeeField->is_required ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">Required Field</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="employee_can_view" class="form-check-input" id="employee_can_view" {{ $employeeField->employee_can_view ? 'checked' : '' }}>
                            <label class="form-check-label" for="employee_can_view">Employee Can View</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="employee_can_edit" class="form-check-input" id="employee_can_edit" {{ $employeeField->employee_can_edit ? 'checked' : '' }}>
                            <label class="form-check-label" for="employee_can_edit">Employee Can Edit</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="hr_can_view" class="form-check-input" id="hr_can_view" {{ $employeeField->hr_can_view ? 'checked' : '' }}>
                            <label class="form-check-label" for="hr_can_view">HR Can View</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="hr_can_edit" class="form-check-input" id="hr_can_edit" {{ $employeeField->hr_can_edit ? 'checked' : '' }}>
                            <label class="form-check-label" for="hr_can_edit">HR Can Edit</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ $employeeField->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Field</button>
                    <a href="{{ route('dynamic-forms.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection