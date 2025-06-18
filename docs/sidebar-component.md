# Dynamic Sidebar Component Documentation

## Overview

The Sidebar component has been modified to be dynamic, allowing different navigation links to be displayed based on the user's role. The component accepts a `menuItems` prop which is an array of navigation items.

## Usage

### Basic Usage

```php
<x-partials.sidebar :menuItems="$menuItems"/>
```

### Menu Item Structure

Each menu item in the array should have the following structure:

```php
[
    'href' => '/dashboard',       // The URL for the link
    'icon' => '<svg>...</svg>',   // SVG icon as HTML string
    'label' => 'Dashboard'        // Text label for the menu item
]
```

### Using the MenuHelper

We've created a `MenuHelper` class to generate menu items based on user roles:

```php
// In a Blade template:
<x-partials.sidebar :menuItems="{{ \App\Helpers\MenuHelper::getMenuItems($userRole) }}"/>

// Or directly in a controller:
$menuItems = \App\Helpers\MenuHelper::getMenuItems(auth()->user()->role);
return view('your-view', ['menuItems' => $menuItems]);
```

## Supported Roles

The MenuHelper provides menu configurations for the following roles:

1. **Admin**
   - Dashboard
   - Profile
   - Users Management
   - Reports
   - Settings

2. **Supplier**
   - Dashboard
   - Profile
   - Produce Management
   - My Orders

3. **Vendor**
   - Dashboard
   - Profile
   - Marketplace
   - My Purchases

4. **Default** (for guests or undefined roles)
   - Dashboard
   - Profile

## Customizing Menu Items

To add new menu items or modify existing ones, edit the `getMenuItems()` method in the `App\Helpers\MenuHelper` class.

## Example

```php
// Adding a new menu item for Admins
case 'admin':
    return array_merge($defaultItems, [
        // ... existing items ...
        [
            'href' => '/new-feature',
            'icon' => '<svg>...</svg>',
            'label' => 'New Feature'
        ]
    ]);
```

## Notes

- The sidebar component automatically handles null or empty menu items
- SVG icons should be properly escaped HTML
- Each menu item requires href, icon, and label properties (with safe defaults provided)
