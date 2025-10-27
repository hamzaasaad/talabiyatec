<?php
namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Str;

class Permission extends SpatiePermission
{
   /**  protected static function booted()
    {
        static::saving(function ($permission) {
            $slug = Str::slug($permission->name, '_');
            $originalSlug = $slug;
            $counter = 1;

            while (static::where('slug', $slug)
                         ->where('id', '!=', $permission->id)
                         ->exists()) {
                $slug = "{$originalSlug}_{$counter}";
                $counter++;
            }

            $permission->slug = $slug;
        });
     }*/

  public static function findByName(string $name, ?string $guardName = null): \Spatie\Permission\Contracts\Permission
{
    return static::where('slug', $name)
        ->where('guard_name', $guardName ?? config('auth.defaults.guard'))
        ->firstOrFail();
}

}

