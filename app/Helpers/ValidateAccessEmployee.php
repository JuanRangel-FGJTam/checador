<?php

namespace App\Helpers;

use App\Models\Employee;
use App\Models\User;

class ValidateAccessEmployee
{

    /**
     * validate if the user has access to the employee
     *
     * @return bool
     */
    static function validateUser(User $user, Employee $employee)
    {
        $__hasAccess = true;
        if($user->level_id > 1)
        {
            $__currentLevel = $user->level_id;

            if($__currentLevel >= 2)
            {
                if($user->general_direction_id != $employee->general_direction_id)
                {
                    $__hasAccess = false;
                }
            }

            if($__currentLevel >= 3)
            {
                if($user->direction_id != $employee->direction_id)
                {
                    $__hasAccess = false;
                }
            }

            if($__currentLevel >= 4)
            {
                if($user->subdirectorate_id != $employee->subdirectorate_id)
                {
                    $__hasAccess = false;
                }
            }

            return $__hasAccess;
        }
    }

}