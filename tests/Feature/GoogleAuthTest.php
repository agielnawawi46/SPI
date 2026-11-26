<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(DepartmentSeeder::class);
    }

    public function test_google_callback_accepts_long_avatar_url_for_new_user(): void
    {
        $longAvatar = 'https://lh3.googleusercontent.com/a-/' . str_repeat('A', 1200) . '=s96-c';

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn('carli@peb.co.id');
        $socialiteUser->shouldReceive('getName')->andReturn('Carli Sitompul');
        $socialiteUser->shouldReceive('getId')->andReturn('112593115842152817062');
        $socialiteUser->shouldReceive('getAvatar')->andReturn($longAvatar);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('profile.complete'));

        $user = User::where('email', 'carli@peb.co.id')->first();
        $this->assertNotNull($user);
        $this->assertSame($longAvatar, $user->avatar);
        $this->assertSame('112593115842152817062', $user->google_id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_updates_long_avatar_url_for_existing_user(): void
    {
        $dept = Department::where('code', 'SPI')->first();
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@peb.co.id',
            'nip' => 'PEB-1001',
            'department_id' => $dept->id,
            'is_active' => true,
        ]);
        $user->assignRole('auditor');

        $longAvatar = 'https://lh3.googleusercontent.com/a-/' . str_repeat('B', 1200) . '=s96-c';

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn('existing@peb.co.id');
        $socialiteUser->shouldReceive('getName')->andReturn('Existing User');
        $socialiteUser->shouldReceive('getId')->andReturn('999888777666555444333');
        $socialiteUser->shouldReceive('getAvatar')->andReturn($longAvatar);

        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertSame($longAvatar, $user->fresh()->avatar);
    }
}
