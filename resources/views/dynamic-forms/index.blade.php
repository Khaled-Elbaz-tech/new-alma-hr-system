@extends('layouts.app')

@section('title', 'Dynamic Form Fields')
@section('page-title', 'Dynamic Form Fields')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <a href="{{ route('dynamic-forms.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Field
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Custom Employee Fields</h5>
    </div>
    <div class="card-body">
        @if($fields->count() > 0)
            @foreach($fieldsBySection as $section => $sectionFields)
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted mb-3">{{ ucfirst($section) }}</h6>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Field Label</th>
                                <th>Field Name</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Employee Can Edit</th>
                                <th>HR Can Edit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sectionFields as $field)
                                <tr>
                                    <td>{{ $field->field_label }}</td>
                                    <td><code>{{ $field->field_name }}</code></td>
                                    <td>{{ ucfirst($field->field_type) }}</td>
                                    <td>{{ $field->is_required ? 'Yes' : 'No' }}</td>
                                    <td>{{ $field->employee_can_edit ? 'Yes' : 'No' }}</td>
                                    <td>{{ $field->hr_can_edit ? 'Yes' : 'No' }}</td>
                                     <td>
                                         <a href="{{ route('dynamic-forms.edit', $field->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                           <form action="{{ route('dynamic-forms.destroy', $field->id) }}" method="POST" class="d-inline">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this field?')">Delete</button>
                                         </form>
                                     </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <p class="text-muted">No custom fields defined yet.</p>
        @endif
    </div>
</div>
@endsection