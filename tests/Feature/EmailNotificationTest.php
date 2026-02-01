<?php

use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('user receives custom email verification notification', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, \App\Notifications\VerifyEmailNotification::class);
});

test('verify email has correct indonesian content and branding', function () {
    $verificationUrl = 'https://fabriku.test/verify-email/123';
    $userName = 'John Doe';

    $mail = new VerifyEmail($verificationUrl, $userName);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Verifikasi Alamat Email Anda')
        ->and($mail->verificationUrl)->toBe($verificationUrl)
        ->and($mail->userName)->toBe($userName);
});

test('user receives custom password reset notification', function () {
    Notification::fake();

    $user = User::factory()->create();

    $user->sendPasswordResetNotification('fake-token-123');

    Notification::assertSentTo($user, \App\Notifications\ResetPasswordNotification::class);
});

test('reset password email has correct indonesian content and branding', function () {
    $resetUrl = 'https://fabriku.test/reset-password/token123?email=test@example.com';
    $userName = 'Jane Doe';

    $mail = new ResetPasswordEmail($resetUrl, $userName);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Reset Password Akun Anda')
        ->and($mail->resetUrl)->toBe($resetUrl)
        ->and($mail->userName)->toBe($userName);
});

test('email templates render correctly', function () {
    $verificationUrl = 'https://fabriku.test/verify';
    $userName = 'Test User';

    $mail = new VerifyEmail($verificationUrl, $userName);
    $content = $mail->content();

    expect($content->view)->toBe('emails.verify-email');

    $resetUrl = 'https://fabriku.test/reset';
    $resetMail = new ResetPasswordEmail($resetUrl, $userName);
    $resetContent = $resetMail->content();

    expect($resetContent->view)->toBe('emails.reset-password');
});
