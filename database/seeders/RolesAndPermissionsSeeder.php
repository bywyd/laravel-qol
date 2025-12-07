<?php

namespace Bywyd\LaravelQol\Database\Seeders;

use Bywyd\LaravelQol\Models\Role;
use Bywyd\LaravelQol\Models\Permission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Posts
            ['name' => 'View Posts', 'slug' => 'view-posts', 'group' => 'posts', 'description' => 'Can view all posts'],
            ['name' => 'Create Posts', 'slug' => 'create-posts', 'group' => 'posts', 'description' => 'Can create new posts'],
            ['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts', 'description' => 'Can edit existing posts'],
            ['name' => 'Delete Posts', 'slug' => 'delete-posts', 'group' => 'posts', 'description' => 'Can delete posts'],
            ['name' => 'Publish Posts', 'slug' => 'publish-posts', 'group' => 'posts', 'description' => 'Can publish posts'],

            // Users
            ['name' => 'View Users', 'slug' => 'view-users', 'group' => 'users', 'description' => 'Can view all users'],
            ['name' => 'Create Users', 'slug' => 'create-users', 'group' => 'users', 'description' => 'Can create new users'],
            ['name' => 'Edit Users', 'slug' => 'edit-users', 'group' => 'users', 'description' => 'Can edit user details'],
            ['name' => 'Delete Users', 'slug' => 'delete-users', 'group' => 'users', 'description' => 'Can delete users'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'group' => 'users', 'description' => 'Can assign roles to users'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'view-settings', 'group' => 'settings', 'description' => 'Can view system settings'],
            ['name' => 'Edit Settings', 'slug' => 'edit-settings', 'group' => 'settings', 'description' => 'Can modify system settings'],

            // Super Admin
            ['name' => 'All Permissions', 'slug' => '*', 'group' => 'admin', 'description' => 'Super admin with all permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Has complete access to all features',
                'level' => 100,
                'is_default' => false,
            ]
        );
        $superAdmin->syncPermissions(['*']);

        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Can manage most features',
                'level' => 50,
                'is_default' => false,
            ]
        );
        $admin->syncPermissions([
            'view-posts', 'create-posts', 'edit-posts', 'delete-posts', 'publish-posts',
            'view-users', 'create-users', 'edit-users', 'manage-roles',
            'view-settings', 'edit-settings',
        ]);

        $editor = Role::firstOrCreate(
            ['slug' => 'editor'],
            [
                'name' => 'Editor',
                'description' => 'Can manage content',
                'level' => 25,
                'is_default' => false,
            ]
        );
        $editor->syncPermissions([
            'view-posts', 'create-posts', 'edit-posts', 'publish-posts',
            'view-users',
        ]);

        $author = Role::firstOrCreate(
            ['slug' => 'author'],
            [
                'name' => 'Author',
                'description' => 'Can create and edit own content',
                'level' => 10,
                'is_default' => false,
            ]
        );
        $author->syncPermissions([
            'view-posts', 'create-posts', 'edit-posts',
        ]);

        $user = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Basic user with limited access',
                'level' => 1,
                'is_default' => true,
            ]
        );
        $user->syncPermissions(['view-posts']);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
