# AureusERP Plugin Ecosystem

This document provides a comprehensive overview of the AureusERP plugin architecture, existing plugins, and roadmap for manufacturing-focused extensions.

## Plugin Architecture Overview

AureusERP uses a **modular plugin architecture** built on Laravel and FilamentPHP, where each plugin is a self-contained package that follows consistent patterns and conventions.

### Core Architecture Patterns

- **Service Provider Pattern**: Each plugin extends `PackageServiceProvider`
- **Filament Plugin Pattern**: UI integration through Filament's plugin system
- **Dependency Injection**: Clean separation of concerns with proper DI
- **Event Sourcing**: Activity logging and state change tracking
- **Multi-tenancy**: Company-scoped data with `company_id`

### Standard Plugin Structure

```
plugin-name/
├── composer.json                      # Package definition and autoloading
├── database/
│   ├── factories/                    # Model factories for testing/seeding
│   ├── migrations/                   # Database schema migrations
│   ├── seeders/                      # Data seeders
│   └── settings/                     # Settings migrations (optional)
├── resources/
│   ├── lang/                         # Translation files
│   └── views/                        # Blade templates
├── routes/                           # Web routes (optional)
│   └── web.php
└── src/
    ├── {PluginName}Plugin.php        # Filament plugin class
    ├── {PluginName}ServiceProvider.php # Laravel service provider
    ├── Enums/                        # Enum classes for constants
    ├── Facades/                      # Custom facades (optional)
    ├── Filament/                     # Filament UI components
    │   ├── Resources/                # CRUD resources
    │   ├── Pages/                    # Custom pages
    │   ├── Clusters/                 # Grouped resources
    │   └── Widgets/                  # Dashboard widgets
    ├── Models/                       # Eloquent models
    ├── Policies/                     # Authorization policies
    ├── Traits/                       # Reusable traits
    └── Settings/                     # Settings classes (optional)
```

### Common Traits and Features

- **HasChatter**: Communication and activity logging
- **HasCustomFields**: Dynamic field addition capability
- **HasLogActivity**: Comprehensive activity tracking
- **HasModifyState**: State change management

## Existing Plugins

### Core System Plugins (Always Installed)

#### Analytics
**Purpose**: Business intelligence and reporting foundation  
**Key Features**:
- Basic analytics data collection
- Record tracking and aggregation
- Foundation for business intelligence reports

**Models**: `Record`  
**Status**: Basic implementation, ready for expansion

#### Chatter
**Purpose**: Internal communication and collaboration platform  
**Key Features**:
- Activity feeds and messaging
- File attachments
- Follower/subscription system
- Cross-entity communication

**Models**: `Message`, `Attachment`, `Follower`  
**Traits**: `HasChatter`, `HasLogActivity`  
**Status**: Mature, widely integrated

#### Fields
**Purpose**: Customizable data structure management  
**Key Features**:
- Dynamic field creation
- Multiple field types (text, select, date, etc.)
- Form, table, and info list integration
- Runtime schema extension

**Models**: `Field`  
**Traits**: `HasCustomFields`  
**Status**: Mature, extensible foundation

#### Security
**Purpose**: Role-based access control and authentication  
**Key Features**:
- Permission management
- Role assignments
- Resource-level security
- Integration with Filament Shield

**Status**: Core security framework

#### Support
**Purpose**: Help desk and documentation system  
**Key Features**:
- Plugin management utilities
- Helper functions and utilities
- Core support infrastructure

**Classes**: `PluginManager`, Helper functions  
**Status**: Foundation utilities

#### Table Views
**Purpose**: Customizable data presentation framework  
**Key Features**:
- Custom table configurations
- Saved views and filters
- User-specific table preferences

**Status**: UI enhancement layer

### Business Logic Plugins

#### Accounts 🔄
**Purpose**: Financial accounting and reporting  
**Key Features**:
- Double-entry bookkeeping
- Multi-currency support
- Tax management with partition system
- Payment processing and reconciliation
- Invoice/Bill lifecycle management
- Chart of accounts with 18+ account types

**Key Models**:
- `Account`: Chart of accounts management
- `Move`: Central accounting entries (invoices, bills, payments)
- `Tax`: Comprehensive tax calculation engine
- `Payment`: Payment processing and reconciliation
- `Journal`: Transaction categorization
- `FiscalPosition`: Regional tax mapping

**Enums**: `AccountType`, `MoveType`, `MoveState`, `PaymentState`, `TaxScope`  
**Dependencies**: Products, Partners  
**Status**: **Mature** - Enterprise-ready accounting system

#### Products 📦
**Purpose**: Product catalog and inventory management  
**Key Features**:
- Configurable products with variants
- Hierarchical category system
- Multi-UOM (Unit of Measure) support
- Dynamic pricing with rules engine
- Supplier information management
- Flexible attribute system

**Key Models**:
- `Product`: Core product entity with variants
- `Category`: Hierarchical product organization
- `ProductAttribute`: Flexible attribute system
- `PriceList/PriceRule`: Dynamic pricing engine
- `ProductSupplier`: Vendor-specific information

**Enums**: `ProductType`, `UOMCategory`  
**Status**: **Mature** - Ready for manufacturing extensions

#### Inventories 📊
**Purpose**: Warehouse and inventory management  
**Key Features**:
- Multi-location inventory tracking
- Advanced stock movement workflows
- Automated reorder point management
- Lot/Serial number tracking
- Complex packaging and logistics
- Dropshipping support

**Key Models**:
- `Warehouse`: Multi-warehouse management
- `Location`: Hierarchical location structure
- `Move`: Stock movements with state tracking
- `Operation`: Picking, packing, shipping operations
- `ProductQuantity`: Real-time inventory tracking
- `Package`: Packaging and logistics management

**Enums**: `LocationType`, `MoveState`, `OperationType`, `ProductTracking`  
**Dependencies**: Products  
**Status**: **Mature** - Enterprise-grade inventory system

#### Partners 🤝
**Purpose**: Contact and relationship management  
**Key Features**:
- Customer and vendor management
- Contact hierarchy (companies, individuals)
- Address and communication management
- Credit limits and payment terms
- Integration with sales/purchase processes

**Key Models**:
- `Partner`: Unified contact management
- `PartnerCategory`: Contact categorization
- `Address`: Multi-address support

**Status**: **Mature** - CRM foundation

#### Sales 💰
**Purpose**: Sales pipeline and opportunity management  
**Key Features**:
- Quote-to-cash process
- Sales order lifecycle management
- Team-based sales organization
- UTM tracking and analytics
- Multi-currency support

**Key Models**:
- `Order`: Sales order management
- `OrderLine`: Line-item details
- `Team`: Sales team organization
- `OrderTemplate`: Quotation templates

**Dependencies**: Products, Partners, Accounts, Inventories  
**Status**: **Mature** - Complete sales cycle

#### Purchases 🛒
**Purpose**: Procurement and purchase order management  
**Key Features**:
- Procure-to-pay process
- Purchase requisition workflow
- Vendor management integration
- Multi-approval workflows
- Integration with inventory receiving

**Key Models**:
- `Order`: Purchase order lifecycle
- `OrderLine`: Purchase line items
- `Requisition`: Purchase request workflow
- `OrderGroup`: Bulk purchasing

**Dependencies**: Products, Partners, Accounts, Inventories  
**Status**: **Mature** - Full procurement cycle

#### Employees 👥
**Purpose**: Human resources and employee management  
**Key Features**:
- Comprehensive employee profiles
- Organizational structure management
- Skill-based competency tracking
- Work schedule and calendar management
- Resume and qualification tracking

**Key Models**:
- `Employee`: Employee profile management
- `Department`: Organizational hierarchy
- `Skill/SkillLevel`: Competency tracking
- `Calendar`: Work schedule management
- `EmployeeJobPosition`: Role assignments

**Enums**: `Gender`, `MaritalStatus`, `DistanceUnit`, `WorkLocation`  
**Dependencies**: Partners  
**Status**: **Mature** - Solid HR foundation

### Application Layer Plugins

#### Blogs 📝
**Purpose**: Content management and blogging  
**Key Features**:
- Post creation and management
- Category and tag organization
- SEO optimization features
- Publishing workflow

**Models**: `Post`, `Category`, `Tag`  
**Status**: Content management ready

#### Contacts 📞
**Purpose**: Enhanced contact management  
**Status**: Contact extension layer

#### Invoices 🧾
**Purpose**: Invoice generation and management  
**Dependencies**: Accounts, Sales  
**Status**: Invoice processing layer

#### Payments 💳
**Purpose**: Payment processing and tracking  
**Dependencies**: Accounts  
**Status**: Payment gateway integration

#### Projects 📋
**Purpose**: Project planning and management  
**Status**: Project management foundation

#### Recruitments 🎯
**Purpose**: Applicant tracking and hiring  
**Dependencies**: Employees  
**Status**: Recruitment workflow

#### Timesheets ⏰
**Purpose**: Time tracking and labor management  
**Dependencies**: Employees, Projects  
**Status**: Time tracking system

#### Time-off 🏖️
**Purpose**: Leave management and tracking  
**Dependencies**: Employees  
**Status**: Leave management system

#### Website 🌐
**Purpose**: Customer-facing website  
**Status**: Frontend web interface

## Manufacturing Plugin Roadmap

### Priority 1: Core Manufacturing Foundation

#### Bill of Materials (BOM) Plugin 🏗️
**Purpose**: Material and component management for manufacturing

**Planned Models**:
```php
// Core BOM Structure
BillOfMaterial           // Main BOM definition
├── product_id           // Finished product
├── version             // Version control
├── type               // Assembly, Kit, Phantom
├── state              // Draft, Active, Obsolete
└── company_id         // Multi-tenant support

BomLine                 // BOM components
├── bom_id             // Parent BOM
├── product_id         // Component product
├── quantity           // Required quantity
├── unit_id           // Unit of measure
├── sequence          // Assembly sequence
├── operation_id      // Manufacturing operation
└── sub_bom_id        // Sub-assembly BOM

BomVersion             // Version control
├── bom_id
├── version_number
├── effective_date
├── expiry_date
└── change_description
```

**Key Features**:
- Multi-level BOM explosion/implosion
- Version control with effective dates
- Engineering change management
- Cost calculation and rollup
- Where-used analysis
- Component substitution support

**Enums**: `BomType`, `BomState`, `ComponentType`, `ChangeType`  
**Dependencies**: Products, Inventories  
**Installation**: `php artisan bom:install`

#### Manufacturing Operations Plugin ⚙️
**Purpose**: Production order and work center management

**Planned Models**:
```php
WorkCenter              // Production resources
├── name               // Work center name
├── type              // Machine, Labor, Mixed
├── capacity          // Daily/hourly capacity
├── efficiency        // Efficiency percentage
├── cost_per_hour     // Operating cost
└── location_id       // Physical location

ManufacturingOrder     // Production orders
├── name              // MO reference
├── product_id        // Product to manufacture
├── quantity          // Quantity to produce
├── bom_id           // Bill of materials
├── routing_id       // Production routing
├── state            // Draft, Ready, Progress, Done
├── scheduled_date   // Planned start date
└── deadline         // Due date

WorkOrder             // Individual operations
├── manufacturing_order_id
├── operation_id     // Routing operation
├── work_center_id   // Assigned work center
├── employee_id      // Assigned operator
├── state           // Pending, Ready, Progress, Done
├── duration_expected // Planned duration
└── duration_actual  // Actual time spent

Routing              // Production sequences
├── name
├── product_id
└── active

RoutingOperation     // Operation steps
├── routing_id
├── sequence        // Operation order
├── name           // Operation name
├── work_center_id // Where performed
├── duration       // Standard time
└── description    // Work instructions
```

**Key Features**:
- Work center capacity planning
- Production order lifecycle management
- Real-time shop floor tracking
- Labor and machine time recording
- Routing flexibility and alternatives
- Integration with inventory consumption

**Enums**: `WorkCenterType`, `OrderState`, `OperationState`  
**Dependencies**: BOM, Products, Inventories, Employees  
**Installation**: `php artisan manufacturing:install`

### Priority 2: Quality and Control Systems

#### Quality Control Plugin 🔍
**Purpose**: Quality assurance and inspection management

**Planned Models**:
```php
QualityPoint           // Inspection points
├── name
├── product_id        // Product being inspected
├── operation_id      // Manufacturing operation
├── type             // Incoming, Production, Final
├── frequency        // Every unit, %, Random
└── active

QualityCheck          // Individual inspections
├── quality_point_id
├── manufacturing_order_id
├── inspector_id     // Employee performing check
├── result          // Pass, Fail, Warning
├── measured_value  // Actual measurement
├── notes          // Inspector comments
└── check_date

QualityAlert         // Non-conformance alerts
├── title
├── description
├── severity        // Low, Medium, High, Critical
├── product_id
├── manufacturing_order_id
├── responsible_id  // Assigned employee
├── state          // Open, Investigation, Resolved
└── root_cause
```

**Key Features**:
- Statistical process control (SPC)
- Non-conformance tracking
- Certificate of analysis generation
- Supplier quality ratings
- Corrective action management

**Dependencies**: Manufacturing, Products, Employees  
**Installation**: `php artisan quality:install`

#### Maintenance Plugin 🔧
**Purpose**: Equipment maintenance and reliability

**Planned Models**:
```php
Equipment             // Maintainable assets
├── name
├── serial_number
├── work_center_id   // Associated work center
├── install_date
├── warranty_date
├── status          // Active, Maintenance, Retired
└── specifications  // JSON metadata

MaintenanceRequest   // Work requests
├── equipment_id
├── type           // Preventive, Corrective, Emergency
├── priority       // Low, Normal, High, Critical
├── description
├── requester_id   // Employee who reported
├── technician_id  // Assigned technician
├── state         // Draft, Scheduled, Progress, Done
├── scheduled_date
└── completion_date

MaintenanceSchedule  // Planned maintenance
├── equipment_id
├── name          // PM task name
├── frequency     // Days between maintenance
├── duration      // Estimated hours
├── instructions  // Work procedures
└── last_performed
```

**Dependencies**: Manufacturing, Employees  
**Installation**: `php artisan maintenance:install`

### Priority 3: Advanced Planning Systems

#### Production Planning Plugin 📈
**Purpose**: Material and capacity requirements planning

**Planned Features**:
- Master Production Schedule (MPS)
- Material Requirements Planning (MRP)
- Capacity Requirements Planning (CRP)
- Finite capacity scheduling
- What-if scenario analysis

#### Shop Floor Control Plugin 📱
**Purpose**: Real-time production monitoring

**Planned Features**:
- Barcode/QR code scanning
- Mobile work order interface
- Real-time production reporting
- Machine integration (IoT)
- Labor data collection

#### Cost Accounting Plugin 💹
**Purpose**: Manufacturing cost management

**Planned Features**:
- Standard vs. actual costing
- Variance analysis
- Work-in-progress tracking
- Cost center accounting
- Activity-based costing (ABC)

## Plugin Development Guidelines

### Technical Standards

1. **Naming Conventions**:
   - Plugin directory: `kebab-case`
   - Namespace: `Webkul\{PluginName}`
   - Models: `PascalCase`
   - Database tables: `{plugin}_{table_name}`

2. **Dependencies**:
   - Declare in `composer.json`
   - Register in ServiceProvider
   - Check during installation

3. **Database Design**:
   - Include `company_id` for multi-tenancy
   - Use UUIDs for external references
   - Include standard timestamps
   - Use soft deletes where appropriate

4. **State Management**:
   - Define clear state enums
   - Implement state transitions
   - Use consistent color coding in Filament

5. **Authorization**:
   - Implement policy classes
   - Use resource-level permissions
   - Integrate with Filament Shield

### Installation Commands

All plugins follow the pattern: `php artisan {plugin-name}:install`

**Current Plugins**:
```bash
php artisan accounts:install
php artisan inventories:install
php artisan employees:install
php artisan sales:install
php artisan purchases:install
# ... etc
```

**Planned Manufacturing Plugins**:
```bash
php artisan bom:install
php artisan manufacturing:install
php artisan quality:install
php artisan maintenance:install
```

### Integration Points

- **Inventory Integration**: Automatic component consumption/production
- **Accounting Integration**: Work-in-progress and manufacturing cost tracking
- **Employee Integration**: Labor assignment and time tracking
- **Product Integration**: BOM relationships and manufacturing specifications
- **Analytics Integration**: Production KPIs and reporting

## Conclusion

The AureusERP plugin ecosystem provides a solid foundation for manufacturing operations. The existing accounting, inventory, product, and employee management systems create an ideal platform for building comprehensive manufacturing functionality.

The modular architecture ensures that manufacturing plugins can be developed incrementally, with each plugin building upon the previous layer while maintaining clean separation of concerns and dependencies.

The roadmap prioritizes core manufacturing needs (BOM, Work Orders) before advancing to specialized systems (Quality, Maintenance, Advanced Planning), ensuring a practical implementation path for manufacturing organizations.