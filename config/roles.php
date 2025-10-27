<?php
return [
    'roles' => [
        'admin' => 'مدير النظام',
        'lab' => 'المعمل',
        'wholesaler' => 'تاجر الجملة',
        'driver' => 'السائق',
        'guest' => 'زائر',
    ],

    'permissions' => [
        'manage_users'     => 'إدارة المستخدمين',
        'manage_orders'    => 'إدارة الطلبات',
        'view_products'    => 'عرض المنتجات',
        'create_products'  => 'إضافة المنتجات',
        'update_products'  => 'تعديل المنتجات',
        'delete_products'  => 'حذف المنتجات',
    ],
    'role_permissions' => [
    'admin' => ['*'], 
    'lab' => ['create_products', 'update_products', 'view_products'],
    'wholesaler' => ['manage_orders', 'view_products'],
    'driver' => ['manage_orders'],
    'guest' => ['view_products'],
],

];
