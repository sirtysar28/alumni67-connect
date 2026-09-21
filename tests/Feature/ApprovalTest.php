<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Alur approval akun: register → pending → (approve|reject) → login.
 */
class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'alumni']);

        $u = User::create([
            'name' => 'Admin Test', 'email' => 'admin@test.id', 'password' => 'password',
            'is_approved' => true,
        ]);
        $u->assignRole('super_admin');

        return $u;
    }

    /** Login dengan captcha benar — helper. */
    private function loginAs(User $user, string $password = 'password')
    {
        session(['login_captcha' => hash('sha256', '10')]);

        return $this->post('/login', [
            'email'    => $user->email,
            'password' => $password,
            'captcha'  => '10',
        ]);
    }

    public function test_register_tidak_langsung_aktif_dan_tidak_auto_login(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'alumni']);
        \App\Models\Angkatan::firstOrCreate(
            ['tahun' => 2003],
            ['nama' => 'Angkatan 2003', 'slug' => 'angkatan-2003']
        );

        $response = $this->post('/register', [
            'name'                  => 'Budi Baru',
            'email'                 => 'budibaru@test.id',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
            'kelas'                 => 'IPA 1',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        $user = User::where('email', 'budibaru@test.id')->first();
        $this->assertNotNull($user);
        $this->assertFalse((bool) $user->is_approved);
    }

    public function test_user_belum_disetujui_tidak_bisa_login(): void
    {
        $u = User::create([
            'name' => 'Pending Coy', 'email' => 'pending@test.id', 'password' => 'password',
            'is_approved' => false,
        ]);

        $this->loginAs($u)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_disetujui_bisa_login(): void
    {
        $u = $this->superAdmin();

        $this->loginAs($u)
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($u);
    }

    /** Server belum jalankan migration is_approved tapi kode baru sudah ter-upload:
     *  login TIDAK boleh memblokir siapa pun (anti dead-lock). */
    public function test_login_tetap_jalan_saat_kolom_is_approved_belum_ada(): void
    {
        \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $t) {
            $t->dropColumn('is_approved');
        });

        $u = User::create([
            'name' => 'Admin Tanpa Kolom', 'email' => 'nokolum@test.id', 'password' => 'password',
        ]);

        $this->loginAs($u)
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($u);
    }

    public function test_admin_bisa_melihat_dan_menyetujui_pendaftar(): void
    {
        $admin = $this->superAdmin();

        $pending = User::create([
            'name' => 'Calon Alumni', 'email' => 'calon@test.id', 'password' => 'password',
            'is_approved' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.pending'))
            ->assertOk()
            ->assertSee('Calon Alumni');

        $this->actingAs($admin)
            ->post(route('admin.users.approve', $pending))
            ->assertRedirect();

        $this->assertTrue((bool) $pending->refresh()->is_approved);
        $this->assertNull($pending->refresh()->approval_note);
    }

    public function test_admin_bisa_menolak_dengan_alasan(): void
    {
        $admin = $this->superAdmin();

        $pending = User::create([
            'name' => 'Calon Ditolsk', 'email' => 'ditolak@test.id', 'password' => 'password',
            'is_approved' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.reject', $pending), ['alasan' => 'Data kelas tidak ditemukan'])
            ->assertRedirect();

        $this->assertFalse((bool) $pending->refresh()->is_approved);
        $this->assertSame('Data kelas tidak ditemukan', $pending->refresh()->approval_note);
    }

    public function test_alumni_biasa_tidak_bisa_buka_halaman_approval(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'alumni']);
        $u = User::create([
            'name' => 'Alumni Biasa', 'email' => 'alumni@test.id', 'password' => 'password',
            'is_approved' => true,
        ]);
        $u->assignRole('alumni');

        $this->actingAs($u)->get(route('admin.users.pending'))->assertForbidden();
    }
}
