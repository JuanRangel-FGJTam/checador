<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;

class UserService {
    
    
    /**
     * get the users stored
     *
     * @return Collection|Array<User>
     */
    public function getUsers(){
        return User::with(['generalDirection', 'direction', 'subdirectorate', 'department' ])->get();
    }
    
    /**
     * getUser
     *
     * @param  string|int $userId
     * @return User
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getUser($userId){
        return User::with(['generalDirection', 'direction', 'subdirectorate', 'department' ])->findOrFail($userId);
    }
    
}