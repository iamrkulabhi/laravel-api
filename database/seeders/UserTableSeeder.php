<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'create articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'author']);
        $role1->givePermissionTo('edit articles');
        $role1->givePermissionTo('create articles');

        $role2 = Role::create(['name' => 'admin']);
        $role2->givePermissionTo('edit articles');
        $role2->givePermissionTo('delete articles');
        $role2->givePermissionTo('publish articles');
        $role2->givePermissionTo('unpublish articles');

        $role3 = Role::create(['name' => 'super-admin']);
        $role3->givePermissionTo('edit articles');
        $role3->givePermissionTo('create articles');
        $role3->givePermissionTo('delete articles');
        $role3->givePermissionTo('publish articles');
        $role3->givePermissionTo('unpublish articles');
        $role3->givePermissionTo('create users');
        $role3->givePermissionTo('edit users');
        $role3->givePermissionTo('delete users');

        // create demo users
        $user = \App\Models\User::factory()->create([
            'name' => 'Rahul Kulabhi',
            'email' => 'rahul.kulabhi69@gmail.com',
            'password' => Hash::make('password')
        ]);
        $user->assignRole($role3);
    }
}
