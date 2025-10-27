<?php 
namespace App\Traits;

trait SingleRoleUser
{
    public function assignSingleRole($role)
    {
        return $this->syncRoles([$role]);
    }

    public function role()
    {
        return $this->roles->first();
    }
}
