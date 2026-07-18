<?php

namespace App\Http\Controllers;

use App\Services\GoogleMeetService;
use Illuminate\Http\Request;

class GoogleOAuthController extends Controller
{
    public function __construct(protected GoogleMeetService $meetService) {}

    /**
     * Redirect admin to Google authorization page
     */
    public function redirect()
    {
        return redirect($this->meetService->getAuthUrl());
    }

    /**
     * Handle the callback from Google after authorization
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect('/admin')->with('error', 'تم رفض تفويض Google Meet.');
        }

        $this->meetService->handleCallback($request->get('code'));

        return redirect('/admin/live-sessions')
            ->with('success', '✅ تم تفويض Google Meet بنجاح! يمكنك الآن إنشاء جلسات.');
    }
}
