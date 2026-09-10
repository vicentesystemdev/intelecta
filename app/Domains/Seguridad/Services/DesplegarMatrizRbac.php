<?php

namespace App\Domains\Seguridad\Services;

use App\Domains\Seguridad\Support\MatrizRbac;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Operator-only deployment, never a demo seeder. Snapshot is mandatory before writing. */
class DesplegarMatrizRbac
{
    public function state(): array
    {
        return [
            'database' => DB::connection()->getDatabaseName(),
            'roles' => DB::table('roles')->orderBy('id')->get(['id', 'name', 'guard_name'])->map(fn ($r) => (array) $r)->all(),
            'permissions' => DB::table('permissions')->orderBy('id')->get(['id', 'name', 'guard_name'])->map(fn ($r) => (array) $r)->all(),
            'pivot' => DB::table('role_has_permissions')->orderBy('role_id')->orderBy('permission_id')->get()->map(fn ($r) => (array) $r)->all(),
        ];
    }

    public function target(): array
    {
        $matrix = [];
        foreach (MatrizRbac::ROLES as $name) {
            $matrix[$name] = MatrizRbac::forRole($name);
            sort($matrix[$name]);
        }
        ksort($matrix);

        return $matrix;
    }

    public function matrix(array $state): array
    {
        $permissions = array_column($state['permissions'], 'name', 'id');
        $roles = array_column($state['roles'], 'name', 'id');
        $matrix = array_fill_keys(array_values($roles), []);
        foreach ($state['pivot'] as $row) {
            $matrix[$roles[$row['role_id']]][] = $permissions[$row['permission_id']];
        }
        foreach ($matrix as &$names) {
            sort($names);
        }
        ksort($matrix);

        return $matrix;
    }

    private function preflight(array $state): void
    {
        $roles = array_column($state['roles'], 'name');
        sort($roles);
        $expected = MatrizRbac::ROLES;
        sort($expected);
        if ($roles !== $expected || array_unique(array_column($state['roles'], 'guard_name')) !== ['web']
            || DB::table('model_has_permissions')->exists()) {
            throw new RuntimeException('Preflight distinto al aprobado: roles, guard o permisos directos. Requiere revisión.');
        }
        foreach ($state['permissions'] as $permission) {
            if ($permission['guard_name'] !== 'web' || ! in_array($permission['name'], MatrizRbac::CATALOG, true)) {
                throw new RuntimeException('Catálogo no reconocido; no se altera la matriz.');
            }
        }
        $missing = array_diff(MatrizRbac::CATALOG, array_column($state['permissions'], 'name'));
        if (array_diff($missing, ['usuarios.asignar_roles'])) {
            throw new RuntimeException('Faltan permisos previos; no se infiere una instalación válida.');
        }
    }

    public function apply(string $snapshotPath): bool
    {
        $changed = app(CuentaService::class)->transaction(function () use ($snapshotPath): bool {
            $before = $this->state();
            $this->preflight($before);
            if ($this->matrix($before) === $this->target()) {
                return false;
            }
            $snapshot = ['version' => 1, 'before' => $before, 'target' => $this->target(), 'operator' => 'deployment-cli', 'created_at' => now()->toIso8601String()];
            $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $file = @fopen($snapshotPath, 'x');
            if ($file === false) {
                throw new RuntimeException('El snapshot debe ser un archivo nuevo en un directorio existente y escribible.');
            }
            try {
                if (fwrite($file, $json) !== strlen($json) || ! fflush($file)) {
                    throw new RuntimeException('No se pudo persistir el snapshot completo; transacción cancelada.');
                }
            } finally {
                fclose($file);
            }
            foreach (MatrizRbac::CATALOG as $permission) {
                Permission::findOrCreate($permission, 'web');
            }
            foreach ($this->target() as $role => $permissions) {
                Role::findByName($role, 'web')->syncPermissions($permissions);
            }
            if ($this->matrix($this->state()) !== $this->target()) {
                throw new RuntimeException('La matriz resultante no coincide con el objetivo.');
            }

            return true;
        });
        // Also invalidate after commit, so another process cannot retain a pre-commit cache entry.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $changed;
    }

    public function restore(string $snapshotPath): bool
    {
        $snapshot = json_decode(file_get_contents($snapshotPath), true, 512, JSON_THROW_ON_ERROR);
        if (($snapshot['version'] ?? null) !== 1 || ($snapshot['target'] ?? null) !== $this->target()) {
            throw new RuntimeException('Snapshot incompatible con este despliegue.');
        }
        $changed = app(CuentaService::class)->transaction(function () use ($snapshot): bool {
            $current = $this->state();
            $before = $snapshot['before'];
            $this->preflight($current);
            $this->preflight($before);
            if ($current['database'] !== $before['database'] || $current['roles'] !== $before['roles']) {
                throw new RuntimeException('El snapshot no corresponde a esta base o los roles cambiaron.');
            }
            if ($current === $before) {
                return false;
            }
            if ($this->matrix($current) !== $snapshot['target']) {
                throw new RuntimeException('La matriz cambió después del despliegue. No se sobrescribe.');
            }
            $beforeById = array_column($before['permissions'], null, 'id');
            $currentById = array_column($current['permissions'], null, 'id');
            foreach ($beforeById as $id => $permission) {
                if (($currentById[$id] ?? null) !== $permission) {
                    throw new RuntimeException('El catálogo cambió después del snapshot.');
                }
            }
            $added = array_diff_key($currentById, $beforeById);
            foreach ($added as $permission) {
                if ($permission['name'] !== 'usuarios.asignar_roles') {
                    throw new RuntimeException('El snapshot no autoriza retirar esta definición.');
                }
            }
            DB::table('role_has_permissions')->delete();
            if ($before['pivot']) {
                DB::table('role_has_permissions')->insert($before['pivot']);
            }
            DB::table('permissions')->whereIn('id', array_keys($added))->delete();
            if ($this->state() !== $before) {
                throw new RuntimeException('El rollback no coincide con el snapshot.');
            }

            return true;
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $changed;
    }
}
