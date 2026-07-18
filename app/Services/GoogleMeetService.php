<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Illuminate\Support\Facades\Storage;

class GoogleMeetService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    /**
     * Get the authorization URL for OAuth
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token and save it
     */
    public function handleCallback(string $code): void
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        $this->client->setAccessToken($token);
        Storage::put('google_token.json', json_encode($token));
    }

    /**
     * Load saved token and refresh if expired
     */
    protected function loadToken(): bool
    {
        if (!Storage::exists('google_token.json')) {
            return false;
        }

        $tokenStr = Storage::get('google_token.json');
        
        if (empty($tokenStr)) {
            return false;
        }

        $token = json_decode($tokenStr, true);

        if (!is_array($token) || empty($token)) {
            return false;
        }

        try {
            $this->client->setAccessToken($token);

            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken(
                        $this->client->getRefreshToken()
                    );
                    Storage::put('google_token.json', json_encode($newToken));
                    $this->client->setAccessToken($newToken);
                } else {
                    return false;
                }
            }
        } catch (\InvalidArgumentException $e) {
            // Token format is invalid or corrupted (e.g. missing access_token key)
            Storage::delete('google_token.json');
            return false;
        }

        return true;
    }

    /**
     * Check if the service is authorized
     */
    public function isAuthorized(): bool
    {
        return $this->loadToken();
    }

    /**
     * Create a Google Meet session via Google Calendar
     *
     * @return array ['meet_link' => string, 'event_id' => string]
     */
    // [شرح أكاديمي للمناقشة]:
    // نستخدم هنا نمط (Service Account Pattern).
    // هذا يعني أننا لا نطلب من كل معلم تسجيل الدخول بحسابه الخاص لإنشاء جلسة.
    // بل يقوم السيرفر بإنشاء الغرفة أوتوماتيكياً على "حساب المدرسة المركزي"
    // ثم يعطي الرابط للمعلم. هذا يسهل العمل، ويجعل جميع الجلسات محفوظة 
    // ومجدولة في تقويم مركزي (Google Calendar) تحت إشراف الإدارة بالكامل.
    public function createMeeting(
        string $title,
        string $description,
        string $startTime,
        string $endTime
    ): array {
        if (!$this->loadToken()) {
            throw new \Exception('Google Meet غير مفوّض. يرجى تفويض الحساب أولاً.');
        }

        $service = new Calendar($this->client);

        $event = new Event([
            'summary'     => $title,
            'description' => $description,
            'start'       => new EventDateTime([
                'dateTime' => $startTime,
                'timeZone' => config('app.timezone', 'Asia/Riyadh'),
            ]),
            'end'         => new EventDateTime([
                'dateTime' => $endTime,
                'timeZone' => config('app.timezone', 'Asia/Riyadh'),
            ]),
            'conferenceData' => new ConferenceData([
                'createRequest' => new CreateConferenceRequest([
                    'requestId'             => uniqid('meet_'),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ]),
            ]),
        ]);

        $createdEvent = $service->events->insert('primary', $event, [
            'conferenceDataVersion' => 1,
        ]);

        $meetLink = $createdEvent->getConferenceData()
            ?->getEntryPoints()[0]
            ?->getUri() ?? '';

        return [
            'meet_link' => $meetLink,
            'event_id'  => $createdEvent->getId(),
        ];
    }

    /**
     * Delete a Google Calendar event (cancel the meeting)
     */
    public function deleteMeeting(string $eventId): void
    {
        if (!$this->loadToken()) return;

        $service = new Calendar($this->client);
        $service->events->delete('primary', $eventId);
    }
}
