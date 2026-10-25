<?php

namespace App\Http\Controllers;

use App\Models\EmployeeField;
use App\Models\EmployeeFieldValue;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DynamicFormController extends Controller
{
    // List all custom fields
    public function index(Request $request)
    {
        $fields = EmployeeField::where('is_active', true)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('field_label', 'like', "%{$search}%")
                        ->orWhere('field_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('field_type') && in_array($request->field_type, ['text', 'textarea', 'number', 'date', 'email', 'phone', 'attachment', 'checkbox', 'radio', 'select', 'yes_no'], true), function ($q) use ($request) {
                $q->where('field_type', $request->field_type);
            })
            ->when($request->filled('field_section'), function ($q) use ($request) {
                $q->where('field_section', $request->field_section);
            })
            ->orderBy('field_order')
            ->orderBy('field_label')
            ->get();

        // Group by section
        $fieldsBySection = $fields->groupBy('field_section');
        $sections = EmployeeField::where('is_active', true)->distinct()->pluck('field_section');

        return view('dynamic-forms.index', compact('fields', 'fieldsBySection', 'sections'));
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
            'new_values' => $request->except(['password', 'password_confirmation']),
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
            'new_values' => $request->except(['password', 'password_confirmation']),
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

    // View/Edit employee custom fields (H3: authorization + employee_can_view enforced)
    public function employeeFields(Employee $employee)
    {
        $user = auth()->user();

        // HR / super_admin may view any employee's fields.
        if (!$user->isHrManager() && !$user->isSuperAdmin()) {
            if ($user->isEmployee()) {
                $own = $user->employee;
                if (!$own || $own->id !== $employee->id) {
                    abort(403, 'Unauthorized to view these fields.');
                }
            } elseif ($user->isManager() || $user->isDM()) {
                // Managers may view only employees of their own managed department.
                $managedDepartment = $user->managedDepartment;
                if (!$managedDepartment || $employee->department_id !== $managedDepartment->id) {
                    abort(403, 'Unauthorized to view these fields.');
                }
            } else {
                abort(403, 'Unauthorized to view these fields.');
            }
        }

        $employee->load('user', 'department', 'fieldValues.field');

        $fieldsQuery = EmployeeField::where('is_active', true)
            ->orderBy('field_order');

        // employee_can_view is enforced for plain-employee viewers (own record only,
        // per the authorization above): fields flagged not employee-viewable are hidden.
        if ($user->isEmployee()) {
            $fieldsQuery->where('employee_can_view', true);
        }

        $fields = $fieldsQuery->get()->groupBy('field_section');

        return view('dynamic-forms.employee-fields', compact('employee', 'fields'));
    }

    // Download employee attachment file (H9: Storage facade imported; path-traversal
    // guarded; M4: download filename sanitized and quoted via Storage::download)
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
                // H3-adjacent: an employee may not download an attachment for a field
                // the field definition marks as not employee-viewable.
                $field = $fieldValue->field;
                if ($field && !$field->employee_can_view) {
                    abort(403, 'Unauthorized to access this file.');
                }
            } else {
                abort(403, 'Unauthorized to access this file.');
            }
        }

        $attachmentPath = $fieldValue->attachment_path;

        // Traversal guard: stored paths must be relative, inside employee_attachments/,
        // and contain no '..', backslashes, absolute-path or drive prefixes. Anything
        // else is treated as not found (never passed to the filesystem).
        if (!$attachmentPath
            || str_contains($attachmentPath, '..')
            || str_contains($attachmentPath, '\\')
            || str_starts_with($attachmentPath, '/')
            || preg_match('/^[A-Za-z]:/', $attachmentPath)
            || !preg_match('#^employee_attachments/[0-9]+/[A-Za-z0-9._-]+$#', $attachmentPath)) {
            abort(404, 'File not found.');
        }

        // New uploads live on the private (local) disk; legacy uploads remain on the
        // public disk and keep downloading. Never serve from a user-supplied path.
        $disk = null;
        if (Storage::disk('local')->exists($attachmentPath)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($attachmentPath)) {
            $disk = 'public';
        } else {
            abort(404, 'File not found.');
        }

        $mimeType = Storage::disk($disk)->mimeType($attachmentPath);
        // M4: strip any characters that could break Content-Disposition; the
        // stored name is already a random hash + allow-listed extension, this is
        // defence in depth. Storage::download() quotes the filename per RFC 6266.
        $safeFileName = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($attachmentPath));

        return Storage::disk($disk)->download($attachmentPath, $safeFileName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
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
            
            // C1: attachment uploads are allow-listed server-side (mime + extension
            // + size). The original filename and the client-supplied mime are never
            // trusted: the file is stored under a random name with only an
            // allow-listed extension, on the private (local) disk, so it is never
            // directly reachable under public/storage (no stored SVG/HTML/PHP risk).
            $attachmentPath = null;
            if ($field->field_type === 'attachment' && $request->hasFile($fieldKey)) {
                $file = $request->file($fieldKey);
                $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions, true)) {
                    return back()->withErrors([$fieldKey => 'Invalid file type. Allowed types: pdf, jpg, jpeg, png, doc, docx.'])->withInput();
                }
                // 'mimes' checks the file's actual content type, not just the name.
                $fileValidator = Validator::make(
                    [$fieldKey => $file],
                    [$fieldKey => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120']
                );
                if ($fileValidator->fails()) {
                    return back()->withErrors($fileValidator)->withInput();
                }
                $attachmentPath = $file->storeAs(
                    'employee_attachments/' . $employee->id,
                    Str::random(40) . '.' . $extension,
                    'local'
                );
            }

            // Only overwrite attachment_path when a new file was actually uploaded;
            // otherwise a fields save without re-uploading must keep the old file.
            $valueData = ['value' => $value];
            if ($attachmentPath !== null) {
                $valueData['attachment_path'] = $attachmentPath;
            }

            EmployeeFieldValue::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'field_id' => $field->id,
                ],
                $valueData
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