<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Models\Role;
use Bywyd\LaravelQol\Models\Permission;
use Bywyd\LaravelQol\Tests\Fixtures\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /** @test */
    public function it_can_assign_role_to_user()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole($role));
    }

    /** @test */
    public function it_can_assign_multiple_roles()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);
        $editor = Role::create(['name' => 'Editor', 'slug' => 'editor', 'level' => 25]);

        $user->assignRole(['admin', 'editor']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasAllRoles(['admin', 'editor']));
    }

    /** @test */
    public function it_can_remove_role_from_user()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);

        $user->assignRole($role);
        $this->assertTrue($user->hasRole('admin'));

        $user->removeRole($role);
        $this->assertFalse($user->hasRole('admin'));
    }

    /** @test */
    public function it_can_sync_roles()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);
        $editor = Role::create(['name' => 'Editor', 'slug' => 'editor', 'level' => 25]);
        $author = Role::create(['name' => 'Author', 'slug' => 'author', 'level' => 10]);

        $user->assignRole(['admin', 'editor']);
        $user->syncRoles(['editor', 'author']);

        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole('author'));
    }

    /** @test */
    public function it_can_check_if_user_has_any_role()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);
        
        $user->assignRole($admin);

        $this->assertTrue($user->hasAnyRole(['admin', 'editor']));
        $this->assertFalse($user->hasAnyRole(['editor', 'author']));
    }

    /** @test */
    public function it_can_give_permission_to_user()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $permission = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);

        $user->givePermission($permission);

        $this->assertTrue($user->hasPermission('edit-posts'));
        $this->assertTrue($user->hasPermission($permission));
    }

    /** @test */
    public function it_can_check_permission_from_role()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);
        $permission = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);

        $role->givePermission($permission);
        $user->assignRole($role);

        $this->assertTrue($user->hasPermission('edit-posts'));
    }

    /** @test */
    public function it_can_revoke_permission_from_user()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $permission = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);

        $user->givePermission($permission);
        $this->assertTrue($user->hasPermission('edit-posts'));

        $user->revokePermission($permission);
        $this->assertFalse($user->hasPermission('edit-posts'));
    }

    /** @test */
    public function it_can_sync_permissions()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $edit = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);
        $delete = Permission::create(['name' => 'Delete Posts', 'slug' => 'delete-posts', 'group' => 'posts']);
        $view = Permission::create(['name' => 'View Posts', 'slug' => 'view-posts', 'group' => 'posts']);

        $user->givePermission(['edit-posts', 'delete-posts']);
        $user->syncPermissions(['delete-posts', 'view-posts']);

        $this->assertFalse($user->hasPermission('edit-posts'));
        $this->assertTrue($user->hasPermission('delete-posts'));
        $this->assertTrue($user->hasPermission('view-posts'));
    }

    /** @test */
    public function super_admin_has_all_permissions()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $role = Role::create(['name' => 'Super Admin', 'slug' => 'super-admin', 'level' => 100]);
        $wildcard = Permission::create(['name' => 'All', 'slug' => '*', 'group' => 'admin']);

        $role->givePermission($wildcard);
        $user->assignRole($role);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->hasPermission('any-permission'));
        $this->assertTrue($user->hasPermission('edit-posts'));
    }

    /** @test */
    public function it_can_get_all_permissions()
    {
        $user = User::create(['name' => 'John', 'email' => 'john@example.com']);
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'level' => 25]);
        
        $edit = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);
        $view = Permission::create(['name' => 'View Posts', 'slug' => 'view-posts', 'group' => 'posts']);
        $delete = Permission::create(['name' => 'Delete Posts', 'slug' => 'delete-posts', 'group' => 'posts']);

        $role->givePermission(['edit-posts', 'view-posts']);
        $user->assignRole($role);
        $user->givePermission('delete-posts');

        $allPermissions = $user->getAllPermissions();

        $this->assertCount(3, $allPermissions);
        $this->assertTrue($allPermissions->contains('slug', 'edit-posts'));
        $this->assertTrue($allPermissions->contains('slug', 'view-posts'));
        $this->assertTrue($allPermissions->contains('slug', 'delete-posts'));
    }

    /** @test */
    public function it_can_filter_users_by_role()
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com']);
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.com']);
        $user = User::create(['name' => 'User', 'email' => 'user@example.com']);

        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin', 'level' => 50]);
        $editorRole = Role::create(['name' => 'Editor', 'slug' => 'editor', 'level' => 25]);

        $admin->assignRole($adminRole);
        $editor->assignRole($editorRole);

        $admins = User::role('admin')->get();
        $editors = User::role('editor')->get();

        $this->assertCount(1, $admins);
        $this->assertCount(1, $editors);
        $this->assertEquals('Admin', $admins->first()->name);
        $this->assertEquals('Editor', $editors->first()->name);
    }

    /** @test */
    public function it_can_filter_users_by_permission()
    {
        $user1 = User::create(['name' => 'User 1', 'email' => 'user1@example.com']);
        $user2 = User::create(['name' => 'User 2', 'email' => 'user2@example.com']);

        $permission = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);

        $user1->givePermission($permission);

        $usersWithPermission = User::permission('edit-posts')->get();

        $this->assertCount(1, $usersWithPermission);
        $this->assertEquals('User 1', $usersWithPermission->first()->name);
    }

    /** @test */
    public function role_can_manage_permissions()
    {
        $role = Role::create(['name' => 'Editor', 'slug' => 'editor', 'level' => 25]);
        $edit = Permission::create(['name' => 'Edit Posts', 'slug' => 'edit-posts', 'group' => 'posts']);
        $delete = Permission::create(['name' => 'Delete Posts', 'slug' => 'delete-posts', 'group' => 'posts']);

        $role->givePermission(['edit-posts', 'delete-posts']);

        $this->assertTrue($role->hasPermission('edit-posts'));
        $this->assertTrue($role->hasPermission('delete-posts'));

        $role->revokePermission('delete-posts');

        $this->assertTrue($role->hasPermission('edit-posts'));
        $this->assertFalse($role->hasPermission('delete-posts'));
    }
}
