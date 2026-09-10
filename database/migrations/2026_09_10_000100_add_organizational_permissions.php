<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    // Frozen migration contract: do not seed users or redistribute existing permissions.
    private const PERMISSIONS = [
        'cargos.ver', 'cargos.crear', 'cargos.editar', 'cargos.cambiar_estado',
        'personal.ver', 'personal.crear', 'personal.editar', 'personal.cambiar_estado',
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $name) {
            if (! DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert(['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()]);
            }
            $permissionId = DB::table('permissions')->where('name', $name)->where('guard_name', 'web')->value('id');
            foreach (DB::table('roles')->where('guard_name', 'web')->whereIn('name', ['Administrador', 'Super Administrador'])->pluck('id') as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Only this module's named permissions; FK pivots are removed by their existing constraints.
        DB::table('permissions')->where('guard_name', 'web')->whereIn('name', self::PERMISSIONS)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
