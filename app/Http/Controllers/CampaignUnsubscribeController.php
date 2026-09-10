<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CampaignUnsubscribeController extends Controller
{
    /**
     * Unsubscribe user from weekly feature campaign emails via signed URL
     */
    public function unsubscribe(Request $request, User $user): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan tidak valid atau telah kedaluwarsa.');
        }

        $user->unsubscribeCampaign();

        $resubscribeUrl = URL::signedRoute('campaign.resubscribe', ['user' => $user->id]);

        return view('campaign.unsubscribed', [
            'user' => $user,
            'tenant' => $user->tenant,
            'resubscribeUrl' => $resubscribeUrl,
        ]);
    }

    /**
     * Handle RFC 8058 One-Click Unsubscribe POST request
     */
    public function handleUnsubscribe(Request $request, User $user): JsonResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan tidak valid.');
        }

        $user->unsubscribeCampaign();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil berhenti berlangganan dari campaign email Fabriku.',
        ]);
    }

    /**
     * Resubscribe user if they unsubscribed accidentally
     */
    public function resubscribe(Request $request, User $user): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Tautan tidak valid atau telah kedaluwarsa.');
        }

        $user->resubscribeCampaign();

        return view('campaign.resubscribed', [
            'user' => $user,
            'tenant' => $user->tenant,
        ]);
    }
}
