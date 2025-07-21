<?php

namespace App\Helpers;

class MenuHelper
{
    /**
     * Get menu items based on user role
     * 
     * @param string $role User role (admin, supplier, vendor)
     * @return array Menu items for the sidebar
     */
    public static function getMenuItems($role = null)
    {
        // Default menu items (accessible to all users)
        $defaultItems = [
            [
                'href' => '/dashboard',
                'icon' => '<svg class="w-6 h-6 text-dashboard-text-light transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg>',
                'label' => 'Dashboard'
            ],
            [
                'href' => '/chat',
                'icon' => '<svg class="w-6 h-6 text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M17 6h-2V5h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2h-.541A5.965 5.965 0 0 1 14 10v4a1 1 0 1 1-2 0v-4c0-2.206-1.794-4-4-4-.075 0-.148.012-.22.028C7.686 6.022 7.596 6 7.5 6A4.505 4.505 0 0 0 3 10.5V16a1 1 0 0 0 1 1h7v3a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-3h5a1 1 0 0 0 1-1v-6c0-2.206-1.794-4-4-4Zm-9 8.5H7a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2Z"/></svg>',
                'label' => 'Inbox',
                'badge' => '<span id="unread-message-count" class="hidden ml-2 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded-full animate-pulse">0</span>'
            ],
        ];

        // Role-specific menu items
        switch ($role) {
            case 'admin':
                return array_merge($defaultItems, [
                    [
                        'href' => '/admin/users',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>',
                        'label' => 'Users Management'
                    ],
                    [
                        'href' => '/orders',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>',
                        'label' => 'Orders Management'
                    ],
                    [
                        'href' => '/inventory',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path></svg>',
                        'label' => 'Inventory Management'
                    ],
                    [
                        'href' => '/SupplyCenters',
                        'icon' => '<svg class="w-8 h-8 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7m-9 4v4m4-4v4m-8-4v4M5 7h14l-1.447-2.894A1 1 0 0016.618 3H7.382a1 1 0 00-.895.553L5 7z" /></path></svg>',
                        'label' => 'Workforce Distribution'
                    ],
                    [
                        'href' => '/reports',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>',
                        'label' => 'Reports'
                    ],
                    [
                        'href' => '/insights',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                        'label' => 'Insights'
                    ],
                ]);

            case 'supplier':
                return array_merge($defaultItems, [
                    [
                        'href' => '/supplier/orders',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>',
                        'label' => 'Orders'
                    ],
                    [
                        'href' => '/supplierInventory',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path></svg>',
                        'label' => 'Inventory'
                    ],
                    [
                        'href' => '/supplier/warehouses',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7m-9 4v4m4-4v4m-8-4v4M5 7h14l-1.447-2.894A1 1 0 0016.618 3H7.382a1 1 0 00-.895.553L5 7z" /></path></svg>',
                        'label' => 'Warehouses'
                    ],
                    [
                        'href' => '/reports/supplier',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>',
                        'label' => 'Reports'
                    ],  
                    
                ]);

            case 'vendor':
                return array_merge($defaultItems, [
                    [
                        'href' => '/vendor/orders',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path></svg>',
                        'label' => 'Orders'
                    ],
                    [
                        'href' => '/vendorInventory',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path></svg>',
                        'label' => 'Inventory'
                    ],
                    [
                        'href' => '/vendor/warehouses',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7m-9 4v4m4-4v4m-8-4v4M5 7h14l-1.447-2.894A1 1 0 0016.618 3H7.382a1 1 0 00-.895.553L5 7z" /></path></svg>',
                        'label' => 'Warehouses'
                    ],
                    [
                        'href' => '/reports/vendor',
                        'icon' => '<svg class="w-6 h-6 text-dashboard-text-light flex-shrink-0 transition duration-75 group-hover:text-dashboard-text-light dark:text-gray-400 dark:group-hover:text-white" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>',
                        'label' => 'Reports'
                    ]
                ]);
                
            default:
                return $defaultItems;
        }
    }
}
