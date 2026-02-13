<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Role\RoleExistsException as RoleExistsException;
use App\Exceptions\Role\RoleHasUsersException as RoleHasUsersException;
use App\Exceptions\Role\RoleProtectedException as RoleProtectedException;


class RoleService
{
    public function createRole($data)
    {
        try {
            DB::beginTransaction();

            $roleName = strtolower(trim($data['name']));
            $existingRole = Role::whereRaw('LOWER(name)=?', [$roleName])->first();
            if ($existingRole) {
                throw new RoleExistsException($data['name']);
            }

            $role = Role::create(['name' => $roleName]);

            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getRoles()
    {
        return Role::select('name')->get();
    }

    public function updateRole($id, $data)
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);

            if (in_array(strtolower($role->name), ['rh', 'manager', 'new_collaborateur'])) {
                throw new RoleProtectedException($role->name);
            }

            $roleName = strtolower(trim($data['name']));
            $existingRole = Role::whereRaw('LOWER(name)=?', [$roleName])
                                ->where('id','!=',$id)
                                ->first();
            if ($existingRole) {
                throw new RoleExistsException($data['name']);
            }

            $role->update(['name' => $roleName]);

            DB::commit();
            return $role;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRole($id)
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);

            if (in_array(strtolower($role->name), ['rh', 'manager', 'new_collaborateur'])) {
                throw new RoleProtectedException($role->name);
            }

            $users = $role->users()->select('nom', 'prenom')->get();
            if ($users->count() > 0) {
                throw new RoleHasUsersException($users);
            }

            $role->delete();
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
