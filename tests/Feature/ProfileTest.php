<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = $this->adminUser();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = $this->adminUser();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->adminUser();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->adminUser();
        $this->adminUser();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSame('inactive', $user->fresh()->status);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->adminUser();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_final_admin_cannot_delete_their_account(): void
    {
        $user = $this->adminUser();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->from('/profile')
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrors('admin')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_admin_can_upload_replace_and_remove_their_own_profile_photo(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();
        $other = $this->adminUser();
        $image = UploadedFile::fake()->createWithContent('admin.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/png');

        $this->actingAs($admin)->put(route('profile.photo'), ['avatar' => $image, 'user_id' => $other->id])->assertSessionHasNoErrors();
        $firstPath = $admin->refresh()->avatar_path;
        $this->assertStringStartsWith('profile-photos/admin/', $firstPath);
        Storage::disk('public')->assertExists($firstPath);
        $this->assertNull($other->refresh()->avatar_path);

        $replacement = UploadedFile::fake()->createWithContent('replacement.webp', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='))->mimeType('image/webp');
        $this->actingAs($admin)->put(route('profile.photo'), ['avatar' => $replacement])->assertSessionHasNoErrors();
        $secondPath = $admin->refresh()->avatar_path;
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $this->actingAs($admin)->delete(route('profile.photo.destroy'))->assertSessionHasNoErrors();
        $this->assertNull($admin->refresh()->avatar_path);
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_admin_profile_photo_rejects_invalid_type_and_oversized_uploads(): void
    {
        $admin = $this->adminUser();
        $this->actingAs($admin)->from(route('profile.edit'))->put(route('profile.photo'), ['avatar' => UploadedFile::fake()->create('not-an-image.pdf', 100, 'application/pdf')])->assertSessionHasErrors('avatar');
        $this->actingAs($admin)->from(route('profile.edit'))->put(route('profile.photo'), ['avatar' => UploadedFile::fake()->image('large.jpg')->size(2049)])->assertSessionHasErrors('avatar');
    }

    private function adminUser(): User
    {
        $role = Role::firstOrCreate(['role_name' => 'admin']);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
