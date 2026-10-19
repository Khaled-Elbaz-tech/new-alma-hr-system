<?php

namespace App\Http\Controllers;

use App\Models\EmployeeField;
use App\Models\EmployeeFieldValue;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DynamicFormController extends Controller
{
    // List all custom fields
    public function index()
    {
        $fields = EmployeeField::where('is_active', true)
            ->orderBy('field_order')
            ->orderBy('field_label')
            ->get();
        
        // Group by section
        $fieldsBySection = $fields->groupBy('field_section');
        
        return view('dynamic-forms.index', compact('fields', 'fieldsBySection'));
    }

    // Create new field
    public function create()
    {
        return view('dynamic-forms.create');
    }

    // Store new field
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_name' => 'required|string|max:255|unique:employee_fields,field_name|regex:/^[a-z_]+$/',
            'field_label' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,date,email,phone,attachment,checkbox,radio,select,yes_no',
            'field_options' => 'nullable|json',
            'field_section' => 'nullable|string|max:100',
            'is_required' => 'boolean',
            'employee_can_view' => 'boolean',
            'employee_can_edit' => 'boolean',
            'hr_can_view' => 'boolean',
            'hr_can_edit' => 'boolean',
            'field_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Parse field_options if it's a string
        $fieldOptions = $request->field_options;
        if ($fieldOptions && is_string($fieldOptions)) {
            $fieldOptions = json_decode($fieldOptions, true);
        }

        $field = EmployeeField::create([
            'field_name' => $request->field_name,
            'field_label' => $request->field_label,
            'field_type' => $request->field_type,
            'field_options' => $fieldOptions,
            'field_section' => $request->field_section ?? 'general',
            'is_required' => $request->is_required ?? false,
            'employee_can_view' => $request->employee_can_view ?? true,
            'employee_can_edit' => $request->employee_can_edit ?? false,
            'hr_can_view' => $request->hr_can_view ?? true,
            'hr_can_edit' => $request->hr_can_edit ?? true,
            'field_order' => $request->field_order ?? 0,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'create',
            'model_type' => EmployeeField::class,
            'model_id' => $field->id,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('dynamic-forms.index')->with('success', 'Field created successfully');
    }

    // Edit field
    public function edit($dynamic_form)
    {
        $employeeField = EmployeeField::findOrFail($dynamic_form);
        return view('dynamic-forms.edit', compact('employeeField'));
    }

    // Update field
    public function update(Request $request, $dynamic_form)
    {
        $employeeField = EmployeeField::findOrFail($dynamic_form);
        $validator = Validator::make($request->all(), [
            'field_label' => 'required|string|max:255',
            'field_options' => 'nullable|json',
            'field_section' => 'nullable|string|max:100',
            'is_required' => 'boolean',
            'employee_can_view' => 'boolean',
            'employee_can_edit' => 'boolean',
            'hr_can_view' => 'boolean',
            'hr_can_edit' => 'boolean',
            'field_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $fieldOptions = $request->field_options;
        if ($fieldOptions && is_string($fieldOptions)) {
            $fieldOptions = json_decode($fieldOptions, true);
        }

        $oldValues = $employeeField->toArray();

        $employeeField->update([
            'field_label' => $request->field_label,
            'field_options' => $fieldOptions,
            'field_section' => $request->field_section ?? 'general',
            'is_required' => $request->is_required ?? false,
            'employee_can_view' => $request->employee_can_view ?? true,
            'employee_can_edit' => $request->employee_can_edit ?? false,
            'hr_can_view' => $request->hr_can_view ?? true,
            'hr_can_edit' => $request->hr_can_edit ?? true,
            'field_order' => $request->field_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => EmployeeField::class,
            'model_id' => $employeeField->id,
            'old_values' => $oldValues,
            'new_values' => $request->all(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('dynamic-forms.index')->with('success', 'Field updated successfully');
    }

    // Delete field (soft delete)
    public function destroy(Request $request, $dynamic_form)
    {
        $employeeField = EmployeeField::findOrFail($dynamic_form);
        $employeeField->update(['is_active' => false]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'model_type' => EmployeeField::class,
            'model_id' => $employeeField->id,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Field deactivated successfully');
    }

    // View/Edit employee custom fields
    public function employeeFields(Employee $employee)
    {
        $employee->load('user', 'department', 'fieldValues.field');
        
        $fields = EmployeeField::where('is_active', true)
            ->orderBy('field_order')
            ->get()
            ->groupBy('field_section');
        
        return view('dynamic-forms.employee-fields', compact('employee', 'fields'));
    }

    // Download employee attachment file
    public function downloadAttachment(EmployeeFieldValue $fieldValue)
    {
        $user = auth()->user();
        
        // Check permissions: HR/Super Admin can download any, employee can only download their own
        if (!$user->isHrManager() && !$user->isSuperAdmin()) {
            if ($user->isEmployee()) {
                $employeeUser = $user->employee;
                if (!$employeeUser || $employeeUser->id !== $fieldValue->employee_id) {
                    abort(403, 'Unauthorized to access this file.');
                }
            } else {
                abort(403, 'Unauthorized to access this file.');
            }
        }
        
        if (!$fieldValue->attachment_path || !Storage::disk('public')->exists($fieldValue->attachment_path)) {
            abort(404, 'File not found.');
        }
        
        $filePath = storage_path('app/public/' . $fieldValue->attachment_path);
        $fileName = basename($filePath);
        $mimeType = Storage::disk('public')->mimeType($fieldValue->attachment_path);
        
        return response()->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    // Update employee custom field values
    public function updateEmployeeFields(Request $request, Employee $employee)
    {
        $user = auth()->user();
        
        // Validate permissions
        $canEdit = false;
        if ($user->isHrManager() || $user->isSuperAdmin()) {
            $canEdit = true;
        } elseif ($user->isEmployee()) {
            $employeeUser = $user->employee;
            if ($employeeUser && $employeeUser->id === $employee->id) {
                // Check if employee can edit
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            abort(403, 'Unauthorized to edit these fields');
        }

        $fields = EmployeeField::where('is_active', true)->get();
        
        foreach ($fields as $field) {
            $fieldKey = 'field_' . $field->id;
            
            // Check if employee can edit this field
            if (!$user->isHrManager() && !$user->isSuperAdmin()) {
                if (!$field->employee_can_edit) {
                    continue;
                }
            }
            
            $value = $request->$fieldKey ?? null;
            
            // Handle file upload
            $attachmentPath = null;
            if ($field->field_type === 'attachment' && $request->hasFile($fieldKey)) {
                $file = $request->file($fieldKey);
                $attachmentPath = $file->store('employee_attachments/' . $employee->id, 'public');
            }
            
            EmployeeFieldValue::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'field_id' => $field->id,
                ],
                [
                    'value' => $value,
                    'attachment_path' => $attachmentPath,
                ]
            );
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update_employee_fields',
            'model_type' => Employee::class,
            'model_id' => $employee->id,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Employee fields updated successfully');
    }
}