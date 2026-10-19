<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeField;

class DynamicFieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // List of attachment/file type dynamic fields to create
        $dynamicFields = [
            [
                'field_name' => 'employment_contract',
                'field_label' => 'Employment Contract',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 1,
            ],
            [
                'field_name' => 'bank_account',
                'field_label' => 'Bank Account',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 2,
            ],
            [
                'field_name' => 'passport',
                'field_label' => 'Passport',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 3,
            ],
            [
                'field_name' => 'id_residence_permit',
                'field_label' => 'ID/Residence Permit',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 4,
            ],
            [
                'field_name' => 'iban_certificate',
                'field_label' => 'IBAN Certificate',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 5,
            ],
            [
                'field_name' => 'certificate_of_introduction',
                'field_label' => 'Certificate of Introduction',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 6,
            ],
            [
                'field_name' => 'cv',
                'field_label' => 'CV',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 7,
            ],
            [
                'field_name' => 'appointment_letter',
                'field_label' => 'Appointment Letter',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 8,
            ],
            [
                'field_name' => 'gosi',
                'field_label' => 'GOSI',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 9,
            ],
            [
                'field_name' => 'medical_insurance',
                'field_label' => 'Medical Insurance',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 10,
            ],
            [
                'field_name' => 'national_address',
                'field_label' => 'National Address',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 11,
            ],
            [
                'field_name' => 'salary_amendment',
                'field_label' => 'Salary Amendment/Increase Letters',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 12,
            ],
            [
                'field_name' => 'vacation',
                'field_label' => 'Vacation',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 13,
            ],
            [
                'field_name' => 'graduation_certificate',
                'field_label' => 'Graduation Certificate',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 14,
            ],
            [
                'field_name' => 'experience_certificates',
                'field_label' => 'Experience Certificates/International Certificates',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 15,
            ],
            [
                'field_name' => 'laptop_property',
                'field_label' => 'Laptop property and Other Devices',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 16,
            ],
            [
                'field_name' => 'exit_reentry_permit',
                'field_label' => 'Exit and Re-entry Permit',
                'field_type' => 'attachment',
                'field_section' => 'documents',
                'is_required' => false,
                'employee_can_view' => true,
                'employee_can_edit' => true,
                'hr_can_view' => true,
                'hr_can_edit' => true,
                'field_order' => 17,
            ],
        ];

        // Create each dynamic field
        foreach ($dynamicFields as $fieldData) {
            // Check if field already exists to avoid duplicates
            $existingField = EmployeeField::where('field_name', $fieldData['field_name'])->first();
            
            if (!$existingField) {
                EmployeeField::create($fieldData);
                echo "Created dynamic field: {$fieldData['field_label']}\n";
            } else {
                echo "Dynamic field already exists: {$fieldData['field_label']}\n";
            }
        }
    }
}