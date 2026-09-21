<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerminalTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        foreach (['super_admin', 'alumni'] as $r) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $r]);
        }

        $u = User::create([
            'name'     => 'Super Admin Test',
            'email'    => 'super@test.id',
            'password' => 'password',
        ]);
        $u->assignRole('super_admin');

        return $u;
    }

    public function test_guest_ditolak(): void
    {
        $this->get('/admin/terminal')->assertRedirect('/login');
    }

    public function test_alumni_biasa_ditolak(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'alumni']);

        $u = User::create([
            'name' => 'Alumni Biasa', 'email' => 'alumni@test.id', 'password' => 'password',
        ]);
        $u->assignRole('alumni');

        $this->actingAs($u)->get('/admin/terminal')->assertForbidden();
        $this->actingAs($u)->post('/admin/terminal', ['command' => 'migrate:status'])->assertForbidden();
    }

    public function test_super_admin_bisa_membuka_terminal(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/terminal')
            ->assertOk()
            ->assertSee('Terminal Artisan');
    }

    public function test_perintah_whitelist_berjalan(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/terminal', ['command' => 'migrate:status'])
            ->assertRedirect();
    }

    public function test_perintah_di_luar_whitelist_ditolak(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/terminal', ['command' => 'tinker'])
            ->assertSessionHas('terminal_error');
    }

    public function test_injeksi_ditolak(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/terminal', ['command' => 'migrate --force; rm -rf /'])
            ->assertSessionHas('terminal_error');
    }

    public function test_seeder_palsu_ditolak(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/terminal', ['command' => 'db:seed --class=EvilSeeder'])
            ->assertSessionHas('terminal_error');
    }

    public function test_setup_roles_buat_dan_promote(): void
    {
        $admin = $this->superAdmin();

        // user baru tanpa role
        $target = User::create([
            'name' => 'Target Promote', 'email' => 'target@test.id', 'password' => 'password',
        ]);

        $this->actingAs($admin)
            ->post('/admin/terminal', ['command' => "setup:roles --user={$target->id}"])
            ->assertRedirect();

        $this->assertTrue($target->refresh()->hasRole('super_admin'));

        foreach (['super_admin', 'pengurus', 'ketua_angkatan', 'alumni'] as $r) {
            $this->assertDatabaseHas('roles', ['name' => $r]);
        }
    }

    public function test_halaman_terminal_render_tombol_grup(): void
    {
        $this->actingAs($this->superAdmin())
            ->get('/admin/terminal')
            ->assertOk()
            ->assertSee('symlink storage → public')
            ->assertSee('🔑 buat roles standar')
            ->assertSee('📚 Database');
    }
}
