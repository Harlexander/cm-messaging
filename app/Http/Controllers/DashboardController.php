<?php

namespace App\Http\Controllers;

use App\Models\EmailDispatch;
use App\Models\EmailDispatchRecipient;
use App\Models\KingschatDispatch;
use App\Models\UserList;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $lastWeek = $now->copy()->subWeek();

        // Email Analytics
        $emailStats = $this->getEmailStats($now, $lastMonth);
        
        // KingsChat Analytics
        $kingschatStats = $this->getKingsChatStats($now, $lastMonth);
        
        // User Analytics
        $userStats = $this->getUserStats($now, $lastMonth);
        
        // Click Analytics
        $clickStats = $this->getClickStats($now, $lastMonth);

        // Weekly Stats
        $weeklyStats = $this->getWeeklyStats($now, $lastWeek);

        // Recent Activity
        $recentEmails = $this->getRecentEmails();
        $recentClicks = $this->getRecentClicks();

        return Inertia::render('dashboard', [
            'analytics' => [
                'emails' => $emailStats,
                'kingschat' => $kingschatStats,
                'users' => $userStats,
                'clicks' => $clickStats,
            ],
            'weeklyStats' => $weeklyStats,
            'recentEmails' => $recentEmails,
            'recentClicks' => $recentClicks,
        ]);
    }

    private function getEmailStats(Carbon $now, Carbon $lastMonth): array
    {
        $stats = EmailDispatch::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as current_month,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as previous_month
        ', [
            $lastMonth->toDateTimeString(),
            $now->toDateTimeString(),
            $lastMonth->copy()->subMonth()->toDateTimeString(),
            $lastMonth->toDateTimeString()
        ])->first();

        $growth = $stats->previous_month > 0 
            ? round((($stats->current_month - $stats->previous_month) / $stats->previous_month) * 100, 1)
            : 100;

        return [
            'total' => $stats->total,
            'growth' => $growth
        ];
    }

    private function getKingsChatStats(Carbon $now, Carbon $lastMonth): array
    {
        $stats = KingschatDispatch::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as current_month,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as previous_month
        ', [
            $lastMonth->toDateTimeString(),
            $now->toDateTimeString(),
            $lastMonth->copy()->subMonth()->toDateTimeString(),
            $lastMonth->toDateTimeString()
        ])->first();

        $growth = $stats->previous_month > 0 
            ? round((($stats->current_month - $stats->previous_month) / $stats->previous_month) * 100, 1)
            : 100;

        return [
            'total' => $stats->total,
            'growth' => $growth
        ];
    }

    private function getUserStats(Carbon $now, Carbon $lastMonth): array
    {
        $stats = UserList::selectRaw('
            COUNT(*) as total,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as current_month,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as previous_month
        ', [
            $lastMonth->toDateTimeString(),
            $now->toDateTimeString(),
            $lastMonth->copy()->subMonth()->toDateTimeString(),
            $lastMonth->toDateTimeString()
        ])->first();

        $growth = $stats->previous_month > 0 
            ? round((($stats->current_month - $stats->previous_month) / $stats->previous_month) * 100, 1)
            : 100;

        return [
            'total' => $stats->total,
            'growth' => $growth
        ];
    }

    private function getClickStats(Carbon $now, Carbon $lastMonth): array
    {
        $stats = EmailDispatchRecipient::selectRaw('
            COUNT(CASE WHEN clicked_at IS NOT NULL THEN 1 END) as total,
            COUNT(CASE WHEN clicked_at BETWEEN ? AND ? THEN 1 END) as current_month,
            COUNT(CASE WHEN clicked_at BETWEEN ? AND ? THEN 1 END) as previous_month
        ', [
            $lastMonth->toDateTimeString(),
            $now->toDateTimeString(),
            $lastMonth->copy()->subMonth()->toDateTimeString(),
            $lastMonth->toDateTimeString()
        ])->first();

        $growth = $stats->previous_month > 0 
            ? round((($stats->current_month - $stats->previous_month) / $stats->previous_month) * 100, 1)
            : 100;

        return [
            'total' => $stats->total,
            'growth' => $growth
        ];
    }

    private function getWeeklyStats(Carbon $now, Carbon $lastWeek): array
    {
        // Get all weekly stats in a single query
        $userStats = UserList::from('prayer_conference')
            ->selectRaw('
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as new_users,
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as previous_users,
                COUNT(CASE WHEN kc_user_id IS NOT NULL AND subscribed_at BETWEEN ? AND ? THEN 1 END) as new_subscribers,
                COUNT(CASE WHEN (kc_user_id IS NOT NULL) AND created_at BETWEEN ? AND ? THEN 1 END) as previous_subscribers
            ', [
                $lastWeek->toDateTimeString(),
                $now->toDateTimeString(),
                $lastWeek->copy()->subWeek()->toDateTimeString(),
                $lastWeek->toDateTimeString(),
                $lastWeek->toDateTimeString(),
                $now->toDateTimeString(),
                $lastWeek->copy()->subWeek()->toDateTimeString(),
                $lastWeek->toDateTimeString()
            ])->first();

        $emailStats = EmailDispatchRecipient::selectRaw('
            COUNT(CASE WHEN opened_at BETWEEN ? AND ? THEN 1 END) as current_opens,
            COUNT(CASE WHEN opened_at BETWEEN ? AND ? THEN 1 END) as previous_opens,
            COUNT(CASE WHEN clicked_at BETWEEN ? AND ? THEN 1 END) as current_clicks,
            COUNT(CASE WHEN clicked_at BETWEEN ? AND ? THEN 1 END) as previous_clicks
        ', [
            $lastWeek->toDateTimeString(),
            $now->toDateTimeString(),
            $lastWeek->copy()->subWeek()->toDateTimeString(),
            $lastWeek->toDateTimeString(),
            $lastWeek->toDateTimeString(),
            $now->toDateTimeString(),
            $lastWeek->copy()->subWeek()->toDateTimeString(),
            $lastWeek->toDateTimeString()
        ])->first();

        // Calculate trends
        $usersTrend = $userStats->previous_users > 0 
            ? round((($userStats->new_users - $userStats->previous_users) / $userStats->previous_users) * 100, 1)
            : 100;

        $subscribersTrend = $userStats->previous_subscribers > 0 
            ? round((($userStats->new_subscribers - $userStats->previous_subscribers) / $userStats->previous_subscribers) * 100, 1)
            : 100;

        $opensTrend = $emailStats->previous_opens > 0 
            ? round((($emailStats->current_opens - $emailStats->previous_opens) / $emailStats->previous_opens) * 100, 1)
            : 100;

        $currentClickRate = $emailStats->current_opens > 0 
            ? round(($emailStats->current_clicks / $emailStats->current_opens) * 100, 1)
            : 0;

        $previousClickRate = $emailStats->previous_opens > 0 
            ? round(($emailStats->previous_clicks / $emailStats->previous_opens) * 100, 1)
            : 0;

        $clickRateTrend = $previousClickRate > 0 
            ? round((($currentClickRate - $previousClickRate) / $previousClickRate) * 100, 1)
            : 100;

        return [
            'new_users' => [
                'count' => $userStats->new_users,
                'trend' => $usersTrend
            ],
            'new_subscribers' => [
                'count' => $userStats->new_subscribers,
                'trend' => $subscribersTrend
            ],
            'email_engagement' => [
                'count' => $emailStats->current_opens,
                'trend' => $opensTrend
            ],
            'click_rate' => [
                'count' => $currentClickRate,
                'trend' => $clickRateTrend
            ]
        ];
    }

    private function getRecentEmails(): array
    {
        return EmailDispatch::withCount([
            'recipients',
            'recipients as opens_count' => function ($query) {
                $query->whereNotNull('opened_at');
            },
            'recipients as clicks_count' => function ($query) {
                $query->whereNotNull('clicked_at');
            }
        ])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($dispatch) {
            return [
                'id' => $dispatch->id,
                'subject' => $dispatch->subject,
                'sent_at' => $dispatch->created_at->format('M d, Y h:i A'),
                'opens' => $dispatch->opens_count,
                'clicks' => $dispatch->clicks_count
            ];
        })
        ->toArray();
    }

    private function getRecentClicks(): array
    {
        return EmailDispatchRecipient::select([
            'email_dispatch_recipients.id',
            'email_dispatch_recipients.email',
            'email_dispatch_recipients.clicked_at',
            'email_dispatch_recipients.clicked_link',
            'email_dispatches.subject',
            'prayer_conference.full_name as name'
        ])
        ->join('email_dispatches', 'email_dispatch_recipients.dispatch_id', '=', 'email_dispatches.id')
        ->leftJoin('prayer_conference', 'email_dispatch_recipients.email', '=', 'prayer_conference.email')
        ->whereNotNull('clicked_at')
        ->orderBy('clicked_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($recipient) {
            return [
                'id' => $recipient->id,
                'user' => $recipient->name ?? $recipient->email,
                'link' => $recipient->clicked_link,
                'clicked_at' => Carbon::parse($recipient->clicked_at)->format('M d, Y h:i A'),
                'source' => $recipient->subject
            ];
        })
        ->toArray();
    }
} 