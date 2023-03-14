<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'user',
                'email' => 'user@app.com',
                'password' => Hash::make('password'),
                'points' => 0,
            ],
            [
                'name' => 'admin',
                'email' => 'admin@app.com',
                'password' => Hash::make('password'),
                'points' => 0,
            ]
        ];

        $roles = [
            [
                'name' => 'user',
                'guard_name' => 'api'
            ],
            [
                'name' => 'admin',
                'guard_name' => 'api'
            ]
        ];

        $permissions = [
            [
                'name' => 'read:product',
                'guard_name' => 'api'
            ],
            [
                'name' => 'add:product',
                'guard_name' => 'api'
            ],
            [
                'name' => 'edit:product',
                'guard_name' => 'api'
            ],
            [
                'name' => 'delete:product',
                'guard_name' => 'api'
            ],
        ];

        foreach ($permissions as $key => $permission) {
            Permission::create($permission);
        }

        foreach ($roles as $key => $role) {
            $save = Role::create($role);

            if ($role['name'] == 'user') {
                $save->givePermissionTo('read:product');
            } else {
                $save->givePermissionTo('read:product');
                $save->givePermissionTo('add:product');
                $save->givePermissionTo('edit:product');
                $save->givePermissionTo('delete:product');
            }
        }

        foreach ($users as $key => $user) {
            $save =User::create($user);
            if ($user['name'] == 'user') {
                $save->assignRole('user');
            } else {
                $save->assignRole('admin');
            }
        }
    }
}
