<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bill of Materials Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the BOM plugin.
    |
    */

    'defaults' => [
        'quantity_to_produce' => 1.0,
        'type' => 'standard',
        'state' => 'draft',
        'waste_percentage' => 0.0,
        'sequence_increment' => 10,
    ],

    'validation' => [
        'max_bom_levels' => 10, // Maximum nested BOM levels
        'max_components_per_bom' => 1000, // Maximum components in a single BOM
        'allow_circular_references' => false, // Prevent circular BOM references
    ],

    'features' => [
        'enable_versioning' => true,
        'enable_effective_dates' => true,
        'enable_cost_calculation' => true,
        'enable_where_used_analysis' => true,
        'enable_bom_explosion' => true,
        'enable_waste_tracking' => true,
    ],

    'permissions' => [
        'bill_of_material' => [
            'view_any_bill::of::material',
            'view_bill::of::material',
            'create_bill::of::material',
            'update_bill::of::material',
            'delete_bill::of::material',
            'delete_any_bill::of::material',
            'force_delete_bill::of::material',
            'force_delete_any_bill::of::material',
            'restore_bill::of::material',
            'restore_any_bill::of::material',
            'replicate_bill::of::material',
            'reorder_bill::of::material',
            'activate_bill::of::material',
            'make_obsolete_bill::of::material',
            'explode_bill::of::material',
            'where_used_bill::of::material',
        ],
        'bom_line' => [
            'view_any_bom::line',
            'view_bom::line',
            'create_bom::line',
            'update_bom::line',
            'delete_bom::line',
            'delete_any_bom::line',
            'force_delete_bom::line',
            'force_delete_any_bom::line',
            'restore_bom::line',
            'restore_any_bom::line',
            'replicate_bom::line',
            'reorder_bom::line',
        ],
    ],

    'reference_generator' => [
        'prefix' => 'BOM',
        'separator' => '-',
        'length' => 6,
        'padding' => '0',
    ],

    'ui' => [
        'default_items_per_page' => 25,
        'show_cost_information' => true,
        'show_effective_dates' => true,
        'collapsible_components' => true,
        'enable_drag_and_drop' => true,
    ],

    'integration' => [
        'sync_with_inventory' => true,
        'sync_with_costing' => true,
        'notify_on_state_change' => true,
        'auto_update_costs' => false,
    ],
];