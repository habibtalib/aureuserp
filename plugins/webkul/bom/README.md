# Bill of Materials (BOM) Plugin

A comprehensive Bill of Materials management plugin for AureusERP, designed to handle complex manufacturing scenarios with multi-level BOMs, versioning, and cost analysis.

## Features

### Core Functionality
- **Multi-level BOMs**: Support for complex nested bill of materials structures
- **BOM Types**: Standard, Kit, Phantom, and Assembly BOMs
- **Component Management**: Detailed component tracking with quantities, units, and sequences
- **State Management**: Draft, Active, Obsolete, and Archived states with controlled transitions
- **Version Control**: Complete versioning system with effective dates and change tracking

### Advanced Features
- **BOM Explosion**: Recursive expansion of multi-level BOMs
- **Where-Used Analysis**: Trace component usage across all BOMs
- **Cost Calculation**: Automatic cost rollup and unit cost calculation
- **Waste Tracking**: Component waste percentage handling
- **Sub-assemblies**: Support for sub-assembly components with their own BOMs
- **By-products**: Track secondary products generated during manufacturing

### Component Types
- **Raw Materials**: Basic materials used in production
- **Components**: Manufactured or purchased parts
- **Sub-assemblies**: Pre-assembled components with their own BOMs
- **Consumables**: Items consumed during production
- **By-products**: Secondary products from manufacturing process

## Installation

### Prerequisites
Ensure the following plugins are installed:
- Products plugin (`php artisan products:install`)
- Inventories plugin (`php artisan inventories:install`)

### Install BOM Plugin
```bash
php artisan bom:install [--force]
```

This command will:
1. Check plugin dependencies
2. Run database migrations
3. Publish configuration files
4. Generate sample data (optional)
5. Set up permissions with Filament Shield

## Database Schema

### Tables Created
- `bom_bill_of_materials`: Main BOM definitions
- `bom_bom_lines`: BOM component lines
- `bom_bom_versions`: Version history tracking

### Key Relationships
- BOM → Product (belongs to)
- BOM → BOM Lines (has many)
- BOM Line → Product (belongs to)
- BOM Line → Sub-BOM (belongs to, optional)
- BOM → Versions (has many)

## Usage

### Creating a BOM
1. Navigate to Manufacturing → Bills of Material
2. Click "Create Bill of Material"
3. Fill in basic information (name, reference, product)
4. Add components using the repeater interface
5. Set component types, quantities, and sequences
6. Save as draft or activate immediately

### BOM States
- **Draft**: Under development, can be edited freely
- **Active**: Approved for production use
- **Obsolete**: No longer current but kept for reference
- **Archived**: Permanently archived

### BOM Types
- **Standard**: Regular manufacturing BOM
- **Kit**: Components sold together without manufacturing
- **Phantom**: Virtual BOM that explodes but isn't manufactured
- **Assembly**: Complex products with sub-assemblies

### Working with Components
- Set sequence numbers for assembly order
- Specify waste percentages for material planning
- Mark components as optional
- Link sub-assemblies to their own BOMs
- Track by-products generated during production

### BOM Analysis
- **Explode BOM**: View all components at all levels
- **Where Used**: See which BOMs use a specific component
- **Cost Analysis**: View material costs and unit costs

## Configuration

The plugin can be configured in `config/bom.php`:

```php
'defaults' => [
    'quantity_to_produce' => 1.0,
    'type' => 'standard',
    'state' => 'draft',
    'sequence_increment' => 10,
],

'validation' => [
    'max_bom_levels' => 10,
    'max_components_per_bom' => 1000,
    'allow_circular_references' => false,
],

'features' => [
    'enable_versioning' => true,
    'enable_cost_calculation' => true,
    'enable_bom_explosion' => true,
],
```

## Permissions

The plugin integrates with Filament Shield for role-based access control:

### BOM Permissions
- `view_any_bill::of::material`
- `view_bill::of::material`
- `create_bill::of::material`
- `update_bill::of::material`
- `delete_bill::of::material`
- `activate_bill::of::material`
- `make_obsolete_bill::of::material`
- `explode_bill::of::material`
- `where_used_bill::of::material`

### BOM Line Permissions
- `view_any_bom::line`
- `create_bom::line`
- `update_bom::line`
- `delete_bom::line`

## API Integration

### Model Relationships
```php
// Get BOM with all components
$bom = BillOfMaterial::with(['bomLines.product', 'bomLines.unit'])->find($id);

// Explode BOM structure
$explosion = $bom->explodeBom($quantity = 10);

// Find where a product is used
$whereUsed = $bom->whereUsed();

// Calculate costs
$totalCost = $bom->getTotalCost();
$unitCost = $bom->getUnitCost();
```

### Scopes
```php
// Active BOMs only
BillOfMaterial::active()->get();

// Effective BOMs for a specific date
BillOfMaterial::effective('2024-01-01')->get();

// BOMs for specific product
BillOfMaterial::forProduct($productId)->get();
```

## Factories and Testing

The plugin includes comprehensive factories for testing:

```php
// Create BOM with components
$bom = BillOfMaterial::factory()
    ->standard()
    ->active()
    ->withComponents(5)
    ->create();

// Create specific component types
BomLine::factory()->material()->create();
BomLine::factory()->subAssembly()->create();
BomLine::factory()->consumable()->withWaste(10)->create();
```

## Integration Points

### Products Plugin
- BOMs are linked to products
- Component selection from product catalog
- Unit of measure integration

### Inventories Plugin
- Material consumption tracking
- Stock reservation for production
- Lot/serial number traceability

### Accounts Plugin
- Cost accounting integration
- Work-in-progress tracking
- Manufacturing cost allocation

## Manufacturing Workflow Integration

This BOM plugin serves as the foundation for:
1. **Manufacturing Orders**: Production planning based on BOMs
2. **Material Requirements Planning (MRP)**: Automatic component demand calculation
3. **Work Orders**: Operation sequencing and work center assignments
4. **Quality Control**: Inspection points and quality requirements
5. **Cost Accounting**: Standard vs. actual cost analysis

## Future Enhancements

### Planned Features
- BOM comparison tools
- Engineering change management
- Alternative component support
- Batch/lot specific BOMs
- Integration with CAD systems
- Mobile BOM viewing

### Manufacturing Extensions
- Work center assignments per component
- Operation routing integration
- Quality inspection points
- Tool and fixture requirements
- Setup and cycle time tracking

## Support and Documentation

- **Plugin Documentation**: See PLUGINS.md in project root
- **API Reference**: Generated from model docblocks
- **Issue Tracking**: Use project issue tracker
- **Customization**: Follow AureusERP plugin development guidelines

## License

This plugin is part of the AureusERP project and follows the same MIT license terms.