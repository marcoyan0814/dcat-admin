<?php

namespace Dcat\Admin\Models;

use Illuminate\Database\Seeder;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createdAt = date('Y-m-d H:i:s');

        // create a user.
        Administrator::truncate();
        Administrator::create([
            'username'   => 'admin',
            'password'   => bcrypt('admin'),
            'name'       => 'Administrator',
            'created_at' => $createdAt,
        ]);

        Administrator::create([
            'username'   => 'webadmin',
            'password'   => bcrypt('webadmin'),
            'name'       => 'Web Admin',
            'created_at' => $createdAt,
        ]);

        // create a role.
        Role::truncate();
        Role::create([
            'name'       => 'Administrator',
            'slug'       => Role::ADMINISTRATOR,
            'created_at' => $createdAt,
        ]);

        Role::create([
            'name'       => 'WebAdmin',
            'slug'       => Role::WEBADMIN,
            'created_at' => $createdAt,
        ]);

        // add role to user.
        Administrator::first()->roles()->save(Role::first());
        // add role to webadmin
        Administrator::find(2)->roles()->save(Role::find(2));

        //create a permission
        Permission::truncate();
        Permission::insert([
            [
                'id'          => 1,
                'name'        => 'Auth management',
                'slug'        => 'auth-management',
                'http_method' => '',
                'http_path'   => '',
                'parent_id'   => 0,
                'order'       => 1,
                'created_at'  => $createdAt,
            ],
            [
                'id'          => 2,
                'name'        => 'Developer Tools',
                'slug'        => 'developer-tools',
                'http_method' => '',
                'http_path'   => '',
                'parent_id'   => 0,
                'order'       => 2,
                'created_at'  => $createdAt,
            ],
            [
                'id'          => 3,
                'name'        => 'Users',
                'slug'        => 'users',
                'http_method' => '',
                'http_path'   => '/auth/users*',
                'parent_id'   => 1,
                'order'       => 3,
                'created_at'  => $createdAt,
            ],
            [
                'id'          => 4,
                'name'        => 'Roles',
                'slug'        => 'roles',
                'http_method' => '',
                'http_path'   => '/auth/roles*',
                'parent_id'   => 1,
                'order'       => 4,
                'created_at'  => $createdAt,
            ],
            [
                'id'          => 5,
                'name'        => 'Permissions',
                'slug'        => 'permissions',
                'http_method' => '',
                'http_path'   => '/auth/permissions*',
                'parent_id'   => 2,
                'order'       => 5,
                'created_at'  => $createdAt,
            ],
            [
                'id'          => 6,
                'name'        => 'Menu',
                'slug'        => 'menu',
                'http_method' => '',
                'http_path'   => '/auth/menu*',
                'parent_id'   => 2,
                'order'       => 6,
                'created_at'  => $createdAt,
            ],
            [
                'id'          => 7,
                'name'        => 'Extension',
                'slug'        => 'extension',
                'http_method' => '',
                'http_path'   => '/auth/extensions*',
                'parent_id'   => 2,
                'order'       => 7,
                'created_at'  => $createdAt,
            ],
        ]);

//        Role::first()->permissions()->save(Permission::first());

        //append permission to webadmin role.
        Role::find(2)->permissions()->saveMany([
            Permission::find(3),
            Permission::find(4),
        ]);

        // add default menus.
        Menu::truncate();
        Menu::insert([
            [
                'parent_id'     => 0,
                'order'         => 1,
                'title'         => 'Index',
                'icon'          => 'feather icon-bar-chart-2',
                'uri'           => '/',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 0,
                'order'         => 2,
                'title'         => 'Admin',
                'icon'          => 'feather icon-settings',
                'uri'           => '',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 0,
                'order'         => 3,
                'title'     => 'Developer Tools',
                'icon'      => 'fa fa-keyboard-o',
                'uri'           => '',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 2,
                'order'         => 4,
                'title'         => 'Users',
                'icon'          => '',
                'uri'           => 'auth/users',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 2,
                'order'         => 5,
                'title'         => 'Roles',
                'icon'          => '',
                'uri'           => 'auth/roles',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 3,
                'order'         => 6,
                'title'         => 'Permission',
                'icon'          => '',
                'uri'           => 'auth/permissions',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 3,
                'order'         => 7,
                'title'         => 'Menu',
                'icon'          => '',
                'uri'           => 'auth/menu',
                'created_at'    => $createdAt,
            ],
            [
                'parent_id'     => 3,
                'order'         => 8,
                'title'         => 'Extensions',
                'icon'          => '',
                'uri'           => 'auth/extensions',
                'created_at'    => $createdAt,
            ],
        ]);

        // append administor role to menu.
        Menu::find(3)->roles()->save(Role::find(1));

        // append webadmin role to menu.
        Role::find(2)->menus()->saveMany([
            Menu::find(1),
            Menu::find(2),
            Menu::find(4),
            Menu::find(5),
        ]);

        (new Menu())->flushCache();
    }
}
