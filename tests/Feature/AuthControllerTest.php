<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    function itRegisterUserWithNotification()
    {
        $this->withoutExceptionHandling();

        //Notification::fake();

        $data = User::factory()->raw([
            'password' => 'K@mikaze83',
            'password_confirmation' => 'K@mikaze83',
            'phone' => '123456'
        ]);

        $response = $this->postJson(
            route('register'),
            $data
        );

        //$user = User::where('email', $data['email'])->first();

        //Notification::assertSentTo($user, VerifyEmail::class);

        $response->assertCreated();
    }

    /**
     * @test
     */
    // public function itVerifyEmail()
    // {
    //     $user = User::factory()->create([
    //         'password' => bcrypt('password'),
    //         'phone' => '123456'
    //     ]);

    //     $url = URL::temporarySignedRoute(
    //         'verification.verify',
    //         Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
    //         [
    //             'id' => $user->getKey(),
    //             'hash' => sha1($user->getEmailForVerification()),
    //             'url' => urlencode("www.test.com")
    //         ]
    //     );
    //     //dd($url);

    //     $response = $this->get($url);

    //     $response->assertRedirect('http://www.test.com?success');
    //     $this->assertNotNull($user->fresh()->email_verified_at);
    // }

    /**
     * @test
     */
    // public function itResendVerificationEmail()
    // {
    //     Notification::fake();
    //     $user = User::factory()->create([
    //         'password' => bcrypt('password'),
    //         'phone' => '123456'
    //     ]);

    //     Sanctum::actingAs($user);

    //     $response = $this->postJson(route('verification.send', ['url' => "www.test.com"]));

    //     $response->assertSuccessful();
    //     Notification::assertSentTo($user, VerifyEmail::class);

    //     $response->assertJsonPath('success', 'a new email verification link has been sent');
    // }
}
