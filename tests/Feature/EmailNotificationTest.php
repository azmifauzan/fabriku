<?php

use App\Mail\ResetPasswordEmail;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;

test('user receives custom email verification notification', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $user->sendEmailVerificationNotification();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

test('verify email notification has correct indonesian content', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'email_verified_at' => null,
    ]);

    $notification = new VerifyEmailNotification();
    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Verifikasi Alamat Email Anda')
        ->and($mailMessage->greeting)->toContain('Halo John Doe')
        ->and($mailMessage->introLines)->toContain('Terima kasih telah mendaftar di Fabriku.')
        ->and($mailMessage->actionText)->toBe('Verifikasi Email')
        ->and($mailMessage->salutation)->toBe('Salam, Tim Fabriku');
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

test('reset password email template renders correctly', function () {
    $resetUrl = 'https://fabriku.test/reset';
    $userName = 'Test User';

    $resetMail = new ResetPasswordEmail($resetUrl, $userName);
    $resetContent = $resetMail->content();

    expect($resetContent->view)->toBe('emails.reset-password');
});
