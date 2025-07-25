<?php

return [
    'plugin_name' => 'Bill of Materials',
    'plugin_description' => 'Comprehensive BOM management for manufacturing operations',

    'navigation' => [
        'group' => 'Manufacturing',
        'bills_of_material' => 'Bills of Material',
    ],

    'models' => [
        'bill_of_material' => 'Bill of Material',
        'bills_of_material' => 'Bills of Material',
        'bom_line' => 'BOM Line',
        'bom_lines' => 'BOM Lines',
        'bom_version' => 'BOM Version',
        'bom_versions' => 'BOM Versions',
    ],

    'fields' => [
        'name' => 'Name',
        'reference' => 'Reference',
        'product' => 'Product',
        'version' => 'Version',
        'type' => 'Type',
        'state' => 'State',
        'quantity_to_produce' => 'Quantity to Produce',
        'unit' => 'Unit',
        'effective_date' => 'Effective Date',
        'expiry_date' => 'Expiry Date',
        'description' => 'Description',
        'notes' => 'Notes',
        'sequence' => 'Sequence',
        'quantity' => 'Quantity',
        'component_type' => 'Component Type',
        'sub_bom' => 'Sub-BOM',
        'waste_percentage' => 'Waste Percentage',
        'is_optional' => 'Optional',
        'version_number' => 'Version Number',
        'change_description' => 'Change Description',
        'change_reason' => 'Change Reason',
        'created_by' => 'Created By',
        'updated_by' => 'Updated By',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'types' => [
        'standard' => 'Standard BOM',
        'kit' => 'Kit BOM',
        'phantom' => 'Phantom BOM',
        'assembly' => 'Assembly BOM',
    ],

    'states' => [
        'draft' => 'Draft',
        'active' => 'Active',
        'obsolete' => 'Obsolete',
        'archived' => 'Archived',
    ],

    'component_types' => [
        'material' => 'Raw Material',
        'component' => 'Component',
        'sub_assembly' => 'Sub-assembly',
        'consumable' => 'Consumable',
        'byproduct' => 'By-product',
    ],

    'actions' => [
        'activate' => 'Activate',
        'make_obsolete' => 'Make Obsolete',
        'explode_bom' => 'Explode BOM',
        'where_used' => 'Where Used',
        'add_component' => 'Add Component',
        'duplicate_bom' => 'Duplicate BOM',
        'create_version' => 'Create Version',
    ],

    'sections' => [
        'basic_information' => 'Basic Information',
        'components' => 'Components',
        'cost_analysis' => 'Cost Analysis',
        'audit_information' => 'Audit Information',
        'version_history' => 'Version History',
    ],

    'messages' => [
        'activated_successfully' => 'BOM activated successfully.',
        'made_obsolete_successfully' => 'BOM made obsolete successfully.',
        'invalid_state_transition' => 'Invalid state transition.',
        'circular_reference_detected' => 'Circular reference detected in BOM structure.',
        'max_levels_exceeded' => 'Maximum BOM levels exceeded.',
        'duplicate_component' => 'Component already exists in this BOM.',
        'sub_bom_required' => 'Sub-BOM is required for sub-assembly components.',
        'effective_date_past' => 'Effective date cannot be in the past.',
        'expiry_before_effective' => 'Expiry date cannot be before effective date.',
    ],

    'filters' => [
        'type' => 'Type',
        'state' => 'State',
        'effective' => 'Currently Effective',
        'product' => 'Product',
        'component_type' => 'Component Type',
    ],

    'widgets' => [
        'bom_overview' => 'BOM Overview',
        'active_boms' => 'Active BOMs',
        'draft_boms' => 'Draft BOMs',
        'cost_summary' => 'Cost Summary',
    ],

    'notifications' => [
        'bom_activated' => 'BOM :reference has been activated.',
        'bom_made_obsolete' => 'BOM :reference has been made obsolete.',
        'bom_created' => 'New BOM :reference has been created.',
        'bom_updated' => 'BOM :reference has been updated.',
    ],

    'validation' => [
        'required' => 'The :attribute field is required.',
        'unique' => 'The :attribute has already been taken.',
        'numeric' => 'The :attribute must be a number.',
        'positive' => 'The :attribute must be positive.',
        'max' => 'The :attribute may not be greater than :max.',
        'date' => 'The :attribute is not a valid date.',
        'after' => 'The :attribute must be a date after :date.',
    ],
];