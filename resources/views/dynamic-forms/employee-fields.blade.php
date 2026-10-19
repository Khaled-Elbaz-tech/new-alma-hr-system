@extends('layouts.app')

@section('title', 'Employee Custom Fields')
@section('page-title', 'Custom Fields: ' . $employee->user->name)

@section('content')
<div class="row">
    <div class="col-md-12 mb-3">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Employee
        </a>
    </div>
</div>

@php
    $isHrOrSuperAdmin = auth()->user()->isHrManager() || auth()->user()->isSuperAdmin();
@endphp

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            DEBUG: HR or Super Admin? {{ $isHrOrSuperAdmin ? 'Yes' : 'No' }}. Fields count: {{ $fields->count() }}
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Custom Field Values</h5>
    </div>
    <div class="card-body">
        @php
            $isHrOrSuperAdmin = auth()->user()->isHrManager() || auth()->user()->isSuperAdmin();
        @endphp

        @if($isHrOrSuperAdmin)
            <!-- Editable form for HR/Super Admin -->
            <form method="POST" action="{{ route('dynamic-forms.employee-fields.update', $employee) }}">
                @csrf
                @method('PUT')
                
                @if($fields->count() > 0)
                    @foreach($fields as $section => $sectionFields)
                        <div class="mb-4">
                            <h6 class="text-uppercase text-muted mb-3 border-bottom pb-2">{{ ucfirst($section) }}</h6>
                            <div class="row">
                                @foreach($sectionFields as $field)
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            {{ $field->field_label }}
                                            @if($field->is_required)<span class="text-danger">*</span>@endif
                                        </label>
                                        
                                        @php
                                            $fieldValue = $employee->fieldValues->where('field_id', $field->id)->first();
                                            $currentValue = $fieldValue ? $fieldValue->value : null;
                                            $options = is_array($field->field_options) ? $field->field_options : [];
                                        @endphp
                                        
                                        @switch($field->field_type)
                                            @case('text')
                                                <input type="text" name="field_{{ $field->id }}" class="form-control" 
                                                       value="{{ $currentValue }}" 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                @break
                                            
                                            @case('textarea')
                                                <textarea name="field_{{ $field->id }}" class="form-control" rows="3"
                                                       {{ $field->is_required ? 'required' : '' }}>{{ $currentValue }}</textarea>
                                                @break
                                            
                                            @case('number')
                                                <input type="number" name="field_{{ $field->id }}" class="form-control" 
                                                       value="{{ $currentValue }}" 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                @break
                                            
                                            @case('date')
                                                <input type="date" name="field_{{ $field->id }}" class="form-control" 
                                                       value="{{ $currentValue }}" 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                @break
                                            
                                            @case('email')
                                                <input type="email" name="field_{{ $field->id }}" class="form-control" 
                                                       value="{{ $currentValue }}" 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                @break
                                            
                                            @case('phone')
                                                <input type="tel" name="field_{{ $field->id }}" class="form-control" 
                                                       value="{{ $currentValue }}" 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                @break
                                            
                                            @case('yes_no')
                                                <select name="field_{{ $field->id }}" class="form-select" {{ $field->is_required ? 'required' : '' }}>
                                                    <option value="">Select...</option>
                                                    <option value="Yes" {{ $currentValue == 'Yes' ? 'selected' : '' }}>Yes</option>
                                                    <option value="No" {{ $currentValue == 'No' ? 'selected' : '' }}>No</option>
                                                </select>
                                                @break
                                            
                                            @case('checkbox')
                                                <div class="form-check">
                                                    <input type="checkbox" name="field_{{ $field->id }}" class="form-check-input" 
                                                           id="field_{{ $field->id }}" value="1" {{ $currentValue ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="field_{{ $field->id }}">Yes</label>
                                                </div>
                                                @break
                                            
                                            @case('select')
                                                <select name="field_{{ $field->id }}" class="form-select" {{ $field->is_required ? 'required' : '' }}>
                                                    <option value="">Select...</option>
                                                    @foreach($options as $option)
                                                        <option value="{{ $option }}" {{ $currentValue == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @break
                                            
                                            @case('radio')
                                                @foreach($options as $option)
                                                    <div class="form-check">
                                                        <input type="radio" name="field_{{ $field->id }}" class="form-check-input" 
                                                               id="field_{{ $field->id }}_{{ $loop->index }}" value="{{ $option }}" 
                                                               {{ $currentValue == $option ? 'checked' : '' }}
                                                               {{ $field->is_required ? 'required' : '' }}>
                                                        <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                                @break
                                            
                                            @case('attachment')
                                                @if($fieldValue && $fieldValue->attachment_path)
                                                    <div class="mb-2">
                                                        <a href="{{ route('dynamic-forms.attachment.download', $fieldValue) }}" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-file"></i> View Current File
                                                        </a>
                                                    </div>
                                                @endif
                                                <input type="file" name="field_{{ $field->id }}" class="form-control" 
                                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                @if($field->is_required && !$currentValue)
                                                    <small class="text-muted">Required</small>
                                                @endif
                                                @break
                                            
                                            @default
                                                <input type="text" name="field_{{ $field->id }}" class="form-control" 
                                                       value="{{ $currentValue }}" 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                        @endswitch
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                @else
                    <p class="text-muted">No custom fields have been configured yet.</p>
                @endif
            </form>
        @else
            <!-- Read-only view for employees -->
            @if($fields->count() > 0)
                @foreach($fields as $section => $sectionFields)
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted mb-3 border-bottom pb-2">{{ ucfirst($section) }}</h6>
                        <div class="row">
                            @foreach($sectionFields as $field)
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>
                                            <strong>{{ $field->field_label }}</strong>
                                            @if($field->is_required)<span class="text-danger">*</span>@endif
                                        </span>
                                        <span>
                                            @php
                                                $fieldValue = $employee->fieldValues->where('field_id', $field->id)->first();
                                                $currentValue = $fieldValue ? $fieldValue->value : null;
                                            @endphp
                                            @if($fieldValue && ($fieldValue->attachment_path || $currentValue))
                                                @switch($field->field_type)
                                                    @case('attachment')
                                                        <a href="{{ route('dynamic-forms.attachment.download', $fieldValue) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-file-download"></i> Download
                                                        </a>
                                                    @break
                                                    @default
                                                        <span class="text-break">{{ $currentValue }}</span>
                                                    @break
                                                @endswitch
                                            @else
                                                <span class="text-muted">No file uploaded</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-muted">No custom fields have been configured yet.</p>
            @endif
        @endif
    </div>
</div>
@endsection
