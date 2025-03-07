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

        return Inertia::render('Dashboard', [
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
        $currentMonthEmails = EmailDispatch::whereBetween('created_at', [$lastMonth, $now])->count();
        $previousMonthEmails = EmailDispatch::whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        
        $growth = $previousMonthEmails > 0 
            ? round((($currentMonthEmails - $previousMonthEmails) / $previousMonthEmails) * 100, 1)
            : 100;

        return [
            'total' => EmailDispatch::count(),
            'growth' => $growth
        ];
    }

    private function getKingsChatStats(Carbon $now, Carbon $lastMonth): array
    {
        $currentMonthMessages = KingschatDispatch::whereBetween('created_at', [$lastMonth, $now])->count();
        $previousMonthMessages = KingschatDispatch::whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        
        $growth = $previousMonthMessages > 0 
            ? round((($currentMonthMessages - $previousMonthMessages) / $previousMonthMessages) * 100, 1)
            : 100;

        return [
            'total' => KingschatDispatch::count(),
            'growth' => $growth
        ];
    }

    private function getUserStats(Carbon $now, Carbon $lastMonth): array
    {
        $currentMonthUsers = UserList::whereBetween('created_at', [$lastMonth, $now])->count();
        $previousMonthUsers = UserList::whereBetween('created_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        
        $growth = $previousMonthUsers > 0 
            ? round((($currentMonthUsers - $previousMonthUsers) / $previousMonthUsers) * 100, 1)
            : 100;

        return [
            'total' => UserList::count(),
            'growth' => $growth
        ];
    }

    private function getClickStats(Carbon $now, Carbon $lastMonth): array
    {
        $currentMonthClicks = EmailDispatchRecipient::whereBetween('clicked_at', [$lastMonth, $now])->count();
        $previousMonthClicks = EmailDispatchRecipient::whereBetween('clicked_at', [$lastMonth->copy()->subMonth(), $lastMonth])->count();
        
        $growth = $previousMonthClicks > 0 
            ? round((($currentMonthClicks - $previousMonthClicks) / $previousMonthClicks) * 100, 1)
            : 100;

        return [
            'total' => EmailDispatchRecipient::whereNotNull('clicked_at')->count(),
            'growth' => $growth
        ];
    }

    private function getWeeklyStats(Carbon $now, Carbon $lastWeek): array
    {
        // New Users
        $newUsers = UserList::whereBetween('created_at', [$lastWeek, $now])->count();
        $previousWeekUsers = UserList::whereBetween('created_at', [$lastWeek->copy()->subWeek(), $lastWeek])->count();
        $usersTrend = $previousWeekUsers > 0 
            ? round((($newUsers - $previousWeekUsers) / $previousWeekUsers) * 100, 1)
            : 100;

        // New Subscribers (Users with either email or KingsChat)
        $newSubscribers = UserList::where(function($query) {
            $query->whereNotNull('kc_user_id');
        })->whereBetween('subscribed_at', [$lastWeek, $now])->count();
        
        $previousWeekSubscribers = UserList::where(function($query) {
            $query->whereNotNull('email')
                  ->orWhereNotNull('kc_user_id');
        })->whereBetween('created_at', [$lastWeek->copy()->subWeek(), $lastWeek])->count();
        
        $subscribersTrend = $previousWeekSubscribers > 0 
            ? round((($newSubscribers - $previousWeekSubscribers) / $previousWeekSubscribers) * 100, 1)
            : 100;

        // Email Engagement (Opens)
        $emailOpens = EmailDispatchRecipient::whereBetween('opened_at', [$lastWeek, $now])->count();
        $previousWeekOpens = EmailDispatchRecipient::whereBetween('opened_at', [$lastWeek->copy()->subWeek(), $lastWeek])->count();
        $opensTrend = $previousWeekOpens > 0 
            ? round((($emailOpens - $previousWeekOpens) / $previousWeekOpens) * 100, 1)
            : 100;

        // Click Rate
        $currentWeekClicks = EmailDispatchRecipient::whereBetween('clicked_at', [$lastWeek, $now])->count();
        $currentWeekOpens = EmailDispatchRecipient::whereBetween('opened_at', [$lastWeek, $now])->count();
        $clickRate = $currentWeekOpens > 0 ? round(($currentWeekClicks / $currentWeekOpens) * 100, 1) : 0;

        $previousWeekClicks = EmailDispatchRecipient::whereBetween('clicked_at', [$lastWeek->copy()->subWeek(), $lastWeek])->count();
        $previousWeekOpens = EmailDispatchRecipient::whereBetween('opened_at', [$lastWeek->copy()->subWeek(), $lastWeek])->count();
        $previousClickRate = $previousWeekOpens > 0 ? round(($previousWeekClicks / $previousWeekOpens) * 100, 1) : 0;

        $clickRateTrend = $previousClickRate > 0 
            ? round((($clickRate - $previousClickRate) / $previousClickRate) * 100, 1)
            : 100;

        return [
            'new_users' => [
                'count' => $newUsers,
                'trend' => $usersTrend
            ],
            'new_subscribers' => [
                'count' => $newSubscribers,
                'trend' => $subscribersTrend
            ],
            'email_engagement' => [
                'count' => $emailOpens,
                'trend' => $opensTrend
            ],
            'click_rate' => [
                'count' => $clickRate,
                'trend' => $clickRateTrend
            ]
        ];
    }

    private function getRecentEmails(): array
    {
        return EmailDispatch::with('recipients')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($dispatch) {
                $opens = $dispatch->recipients()->whereNotNull('opened_at')->count();
                $clicks = $dispatch->recipients()->whereNotNull('clicked_at')->count();
                
                return [
                    'id' => $dispatch->id,
                    'subject' => $dispatch->subject,
                    'sent_at' => $dispatch->created_at->format('M d, Y h:i A'),
                    'opens' => $opens,
                    'clicks' => $clicks
                ];
            })
            ->toArray();
    }

    private function getRecentClicks(): array
    {
        return EmailDispatchRecipient::with(['dispatch', 'user'])
            ->whereNotNull('clicked_at')
            ->orderBy('clicked_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($recipient) {
                return [
                    'id' => $recipient->id,
                    'user' => $recipient->user ? $recipient->user->name : $recipient->email,
                    'link' => $recipient->clicked_link,
                    'clicked_at' => $recipient->clicked_at->format('M d, Y h:i A'),
                    'source' => $recipient->dispatch->subject
                ];
            })
            ->toArray();
    }
} 