<?php

namespace App\Data;

class CatalogMenu {

    static $menus = [
        'dashboard' => 'dashboard',
        'admin' => 'admin.index',
        'rh' => 'employees.index',
        'reports' => 'reports.index',
        'newEmployees' => 'newEmployees.index',
        'show' => 'staff.index',
        'incidents' => 'incidents.index',
        'hollidays' => 'hollidays.create',
        'inactive' => 'inactive.index',
        'justifications' => 'justifications.index'
    ];

}