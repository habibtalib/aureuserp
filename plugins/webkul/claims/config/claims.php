<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Claims Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the Claims module
    |
    */

    'max_claim_amount' => 10000.00,
    'auto_approve_threshold' => 100.00,
    'require_receipts' => true,
    'claim_number_prefix' => 'CLM-',
    
    'approval_levels' => [
        1 => 500.00,   // Manager approval required above this amount
        2 => 2000.00,  // Senior Manager approval required above this amount
        3 => 5000.00,  // Director approval required above this amount
    ],

    'reimbursement_categories' => [
        'travel' => 'Travel & Transportation',
        'meals' => 'Meals & Entertainment',
        'accommodation' => 'Accommodation',
        'office_supplies' => 'Office Supplies',
        'training' => 'Training & Development',
        'medical' => 'Medical Expenses',
        'communication' => 'Communication',
        'other' => 'Other',
    ],

    'default_currency' => 'USD',
];