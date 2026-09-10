<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

class FeatureCampaignEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new message instance.
     *
     * @param  array<string, mixed>  $campaignData
     */
    public function __construct(
        public Tenant $tenant,
        public User $adminUser,
        public array $campaignData,
        public ?string $unsubscribeUrl = null
    ) {
        $this->unsubscribeUrl ??= $adminUser->id > 0
            ? URL::signedRoute('campaign.unsubscribe', ['user' => $adminUser->id])
            : url('/');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaignData['subject'] ?? 'Tips & Fitur Fabriku untuk Bisnis Anda',
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        if ($this->unsubscribeUrl) {
            return new Headers(
                text: [
                    'List-Unsubscribe' => '<' . $this->unsubscribeUrl . '>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feature-campaign',
            with: [
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
