<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        return view('site.pages.home');
    }

    public function about()
    {
        return view('site.pages.about');
    }

    public function programs()
    {
        return view('site.pages.programs');
    }

    public function entrepreneurship()
    {
        return view('site.pages.entrepreneurship');
    }

    public function team()
    {
        return view('site.pages.team');
    }

    public function gallery()
    {
        return view('site.pages.gallery');
    }

    public function contact()
    {
        return view('site.pages.contact');
    }

    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        \Log::info('New contact form submission', $validated);

        return back()->with('success', 'شكراً لتواصلك معنا! سيتم الرد عليك في أقرب وقت ممكن.');
    }
}
