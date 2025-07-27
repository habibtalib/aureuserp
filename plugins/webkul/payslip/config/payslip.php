<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payslip Configuration
    |--------------------------------------------------------------------------
    */

    // Payslip numbering
    'payslip_number_prefix' => env('PAYSLIP_NUMBER_PREFIX', 'PS-'),
    'payslip_number_length' => env('PAYSLIP_NUMBER_LENGTH', 6),

    // PDF Configuration
    'pdf' => [
        'orientation' => 'portrait',
        'paper_size' => 'a4',
        'company_logo_path' => env('PAYSLIP_COMPANY_LOGO', 'images/logo.png'),
        'show_company_address' => env('PAYSLIP_SHOW_COMPANY_ADDRESS', true),
        'watermark' => env('PAYSLIP_WATERMARK', false),
    ],

    // Salary Components Configuration
    'salary_components' => [
        'earnings' => [
            'basic_salary' => [
                'name' => 'Basic Salary',
                'code' => 'BASIC',
                'type' => 'earning',
                'taxable' => true,
                'provident_fund_applicable' => true,
                'order' => 1,
            ],
            'house_rent_allowance' => [
                'name' => 'House Rent Allowance',
                'code' => 'HRA',
                'type' => 'earning',
                'taxable' => true,
                'provident_fund_applicable' => false,
                'order' => 2,
            ],
            'transport_allowance' => [
                'name' => 'Transport Allowance',
                'code' => 'TA',
                'type' => 'earning',
                'taxable' => true,
                'provident_fund_applicable' => false,
                'order' => 3,
            ],
            'medical_allowance' => [
                'name' => 'Medical Allowance',
                'code' => 'MA',
                'type' => 'earning',
                'taxable' => false,
                'provident_fund_applicable' => false,
                'order' => 4,
            ],
            'overtime' => [
                'name' => 'Overtime',
                'code' => 'OT',
                'type' => 'earning',
                'taxable' => true,
                'provident_fund_applicable' => true,
                'order' => 5,
            ],
            'bonus' => [
                'name' => 'Bonus',
                'code' => 'BONUS',
                'type' => 'earning',
                'taxable' => true,
                'provident_fund_applicable' => true,
                'order' => 6,
            ],
        ],
        'deductions' => [
            'provident_fund' => [
                'name' => 'Provident Fund',
                'code' => 'PF',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'rate' => 12.0, // 12% of basic + allowances
                'order' => 1,
            ],
            'professional_tax' => [
                'name' => 'Professional Tax',
                'code' => 'PT',
                'type' => 'deduction',
                'calculation_type' => 'fixed',
                'amount' => 200.0,
                'order' => 2,
            ],
            'income_tax' => [
                'name' => 'Income Tax (TDS)',
                'code' => 'TDS',
                'type' => 'deduction',
                'calculation_type' => 'computed',
                'order' => 3,
            ],
            'esi' => [
                'name' => 'Employee State Insurance',
                'code' => 'ESI',
                'type' => 'deduction',
                'calculation_type' => 'percentage',
                'rate' => 0.75, // 0.75% of gross salary
                'order' => 4,
            ],
            'advance' => [
                'name' => 'Advance Deduction',
                'code' => 'ADV',
                'type' => 'deduction',
                'calculation_type' => 'variable',
                'order' => 5,
            ],
            'loan_emi' => [
                'name' => 'Loan EMI',
                'code' => 'LOAN',
                'type' => 'deduction',
                'calculation_type' => 'variable',
                'order' => 6,
            ],
        ],
    ],

    // Tax Slabs (Indian Tax System - can be customized)
    'tax_slabs' => [
        [
            'min_amount' => 0,
            'max_amount' => 250000,
            'tax_rate' => 0,
        ],
        [
            'min_amount' => 250001,
            'max_amount' => 500000,
            'tax_rate' => 5,
        ],
        [
            'min_amount' => 500001,
            'max_amount' => 1000000,
            'tax_rate' => 20,
        ],
        [
            'min_amount' => 1000001,
            'max_amount' => null,
            'tax_rate' => 30,
        ],
    ],

    // Pay Periods
    'pay_periods' => [
        'monthly' => 'Monthly',
        'bi_weekly' => 'Bi-Weekly',
        'weekly' => 'Weekly',
        'annual' => 'Annual',
    ],

    // Default Pay Period
    'default_pay_period' => 'monthly',

    // Attendance Integration
    'attendance_integration' => [
        'enabled' => true,
        'working_days_per_month' => 22,
        'working_hours_per_day' => 8,
        'overtime_multiplier' => 1.5,
    ],

    // Email Configuration
    'email' => [
        'send_payslip_on_generation' => env('PAYSLIP_AUTO_EMAIL', true),
        'email_template' => 'payslip::emails.payslip',
        'from_name' => env('PAYSLIP_FROM_NAME', 'HR Department'),
        'from_email' => env('PAYSLIP_FROM_EMAIL', 'hr@company.com'),
    ],

    // Security
    'security' => [
        'encrypt_salary_data' => env('PAYSLIP_ENCRYPT_SALARY', true),
        'pdf_password_protection' => env('PAYSLIP_PDF_PASSWORD', false),
        'access_log' => env('PAYSLIP_ACCESS_LOG', true),
    ],
];