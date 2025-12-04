<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_assignment","view_any_assignment","create_assignment","update_assignment","restore_assignment","restore_any_assignment","replicate_assignment","reorder_assignment","delete_assignment","delete_any_assignment","force_delete_assignment","force_delete_any_assignment","view_book","view_any_book","create_book","update_book","restore_book","restore_any_book","replicate_book","reorder_book","delete_book","delete_any_book","force_delete_book","force_delete_any_book","book:create_book","book:update_book","book:delete_book","book:pagination_book","book:detail_book","view_client","view_any_client","create_client","update_client","restore_client","restore_any_client","replicate_client","reorder_client","delete_client","delete_any_client","force_delete_client","force_delete_any_client","view_daily::report","view_any_daily::report","create_daily::report","update_daily::report","restore_daily::report","restore_any_daily::report","replicate_daily::report","reorder_daily::report","delete_daily::report","delete_any_daily::report","force_delete_daily::report","force_delete_any_daily::report","view_equipment::inspection","view_any_equipment::inspection","create_equipment::inspection","update_equipment::inspection","restore_equipment::inspection","restore_any_equipment::inspection","replicate_equipment::inspection","reorder_equipment::inspection","delete_equipment::inspection","delete_any_equipment::inspection","force_delete_equipment::inspection","force_delete_any_equipment::inspection","view_fuel::log","view_any_fuel::log","create_fuel::log","update_fuel::log","restore_fuel::log","restore_any_fuel::log","replicate_fuel::log","reorder_fuel::log","delete_fuel::log","delete_any_fuel::log","force_delete_fuel::log","force_delete_any_fuel::log","view_heavy::equipment","view_any_heavy::equipment","create_heavy::equipment","update_heavy::equipment","restore_heavy::equipment","restore_any_heavy::equipment","replicate_heavy::equipment","reorder_heavy::equipment","delete_heavy::equipment","delete_any_heavy::equipment","force_delete_heavy::equipment","force_delete_any_heavy::equipment","view_incident::report","view_any_incident::report","create_incident::report","update_incident::report","restore_incident::report","restore_any_incident::report","replicate_incident::report","reorder_incident::report","delete_incident::report","delete_any_incident::report","force_delete_incident::report","force_delete_any_incident::report","view_mechanic","view_any_mechanic","create_mechanic","update_mechanic","restore_mechanic","restore_any_mechanic","replicate_mechanic","reorder_mechanic","delete_mechanic","delete_any_mechanic","force_delete_mechanic","force_delete_any_mechanic","view_operator","view_any_operator","create_operator","update_operator","restore_operator","restore_any_operator","replicate_operator","reorder_operator","delete_operator","delete_any_operator","force_delete_operator","force_delete_any_operator","view_project","view_any_project","create_project","update_project","restore_project","restore_any_project","replicate_project","reorder_project","delete_project","delete_any_project","force_delete_project","force_delete_any_project","view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_token","view_any_token","create_token","update_token","restore_token","restore_any_token","replicate_token","reorder_token","delete_token","delete_any_token","force_delete_token","force_delete_any_token","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","view_work::order","view_any_work::order","create_work::order","update_work::order","restore_work::order","restore_any_work::order","replicate_work::order","reorder_work::order","delete_work::order","delete_any_work::order","force_delete_work::order","force_delete_any_work::order","page_ManageSetting","page_Themes","page_MyProfilePage"]},{"name":"operator","guard_name":"web","permissions":["view_assignment","view_any_assignment","view_daily::report","view_any_daily::report","create_daily::report","update_daily::report","restore_daily::report","restore_any_daily::report","replicate_daily::report","reorder_daily::report","delete_daily::report","delete_any_daily::report","force_delete_daily::report","force_delete_any_daily::report","view_fuel::log","view_any_fuel::log","create_fuel::log","update_fuel::log","restore_fuel::log","restore_any_fuel::log","replicate_fuel::log","reorder_fuel::log","delete_fuel::log","delete_any_fuel::log","force_delete_fuel::log","force_delete_any_fuel::log","view_incident::report","view_any_incident::report","create_incident::report","update_incident::report","restore_incident::report","restore_any_incident::report","replicate_incident::report","reorder_incident::report","delete_incident::report","delete_any_incident::report","force_delete_incident::report","force_delete_any_incident::report"]},{"name":"Komandan","guard_name":"web","permissions":["view_assignment","view_any_assignment","view_client","view_any_client","view_daily::report","view_any_daily::report","view_heavy::equipment","view_any_heavy::equipment","view_incident::report","view_any_incident::report","view_project","view_any_project"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
