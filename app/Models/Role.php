<?php
namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Str;

class Role extends SpatieRole
{
   /**  protected static function booted()
    {
        static::saving(function ($role) {
            $slug = Str::slug($role->name, '_');
            $originalSlug = $slug;
            $counter = 1;

            while (static::where('slug', $slug)
                         ->where('id', '!=', $role->id)
                         ->exists()) {
                $slug = "{$originalSlug}_{$counter}";
                $counter++;
            }

            $role->slug = $slug;
        });
    }*/

  public static function findByName(string $name, ?string $guardName = null): \Spatie\Permission\Contracts\Role
{
    return static::where('slug', $name)
        ->where('guard_name', $guardName ?? config('auth.defaults.guard'))
        ->firstOrFail();
}

}

