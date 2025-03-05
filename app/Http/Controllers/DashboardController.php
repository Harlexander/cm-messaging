<?php

namespace App\Http\Controllers;

use App\Models\KingschatDispatch;
use App\Models\UserList;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Monthly analytics
        $analytics = [
            'emails' => [
                'total' => 2543,
                'growth' => 20.1
            ],
            'kingschat' => [
                'total' => KingschatDispatch::count(),
                'growth' => 15.5
            ],
            'users' => [
                'total' => UserList::count(),
                'growth' => 2.5
            ],
            'clicks' => [
                'total' => 573,
                'growth' => 8.2
            ]
        ];

        // Weekly stats
        $weeklyStats = [
            'new_users' => [
                'count' => UserList::where('created_at', '>=', now()->subWeek())->count(),
                'trend' => 12.5
            ],
            'new_subscribers' => [
                'count' => 156,
                'trend' => -5.2
            ],
            'email_engagement' => [
                'count' => 68,
                'trend' => 15.8
            ],
            'click_rate' => [
                'count' => 42,
                'trend' => 8.9
            ]
        ];

        // Recent emails with detailed analytics
        $recentEmails = [
            [
                'id' => 1,
                'subject' => 'Welcome to Our Monthly Newsletter',
                'sent_at' => '2 hours ago',
                'opens' => 450,
                'clicks' => 123,
                'delivered' => 1200,
                'bounced' => 15,
                'spam' => 2,
                'click_rate' => 27.3,
                'open_rate' => 37.5,
                'engagement_time' => '2m 45s',
                'top_locations' => [
                    ['location' => 'Lagos, Nigeria', 'count' => 180],
                    ['location' => 'Abuja, Nigeria', 'count' => 120],
                    ['location' => 'Port Harcourt, Nigeria', 'count' => 85],
                    ['location' => 'Accra, Ghana', 'count' => 65]
                ],
                'click_map' => [
                    ['link' => 'https://example.com/event-registration', 'clicks' => 45],
                    ['link' => 'https://example.com/download-resources', 'clicks' => 38],
                    ['link' => 'https://example.com/watch-video', 'clicks' => 25],
                    ['link' => 'https://example.com/learn-more', 'clicks' => 15]
                ]
            ],
            [
                'id' => 2,
                'subject' => 'Special Event Announcement',
                'sent_at' => '5 hours ago',
                'opens' => 380,
                'clicks' => 95,
                'delivered' => 1000,
                'bounced' => 12,
                'spam' => 1,
                'click_rate' => 25.0,
                'open_rate' => 38.0,
                'engagement_time' => '1m 55s',
                'top_locations' => [
                    ['location' => 'Lagos, Nigeria', 'count' => 150],
                    ['location' => 'Abuja, Nigeria', 'count' => 100],
                    ['location' => 'Accra, Ghana', 'count' => 80],
                    ['location' => 'Nairobi, Kenya', 'count' => 50]
                ],
                'click_map' => [
                    ['link' => 'https://example.com/register-now', 'clicks' => 40],
                    ['link' => 'https://example.com/view-schedule', 'clicks' => 30],
                    ['link' => 'https://example.com/hotel-booking', 'clicks' => 25]
                ]
            ],
            [
                'id' => 3,
                'subject' => 'Important Updates for Members',
                'sent_at' => '8 hours ago',
                'opens' => 520,
                'clicks' => 145,
                'delivered' => 1150,
                'bounced' => 8,
                'spam' => 0,
                'click_rate' => 27.9,
                'open_rate' => 45.2,
                'engagement_time' => '3m 15s',
                'top_locations' => [
                    ['location' => 'Lagos, Nigeria', 'count' => 200],
                    ['location' => 'Abuja, Nigeria', 'count' => 150],
                    ['location' => 'London, UK', 'count' => 100],
                    ['location' => 'New York, USA', 'count' => 70]
                ],
                'click_map' => [
                    ['link' => 'https://example.com/member-portal', 'clicks' => 60],
                    ['link' => 'https://example.com/update-profile', 'clicks' => 45],
                    ['link' => 'https://example.com/resources', 'clicks' => 40]
                ]
            ],
            [
                'id' => 4,
                'subject' => 'Your Weekly Digest',
                'sent_at' => '1 day ago',
                'opens' => 620,
                'clicks' => 210,
                'delivered' => 1300,
                'bounced' => 20,
                'spam' => 3,
                'click_rate' => 33.9,
                'open_rate' => 47.7,
                'engagement_time' => '4m 30s',
                'top_locations' => [
                    ['location' => 'Lagos, Nigeria', 'count' => 250],
                    ['location' => 'Abuja, Nigeria', 'count' => 180],
                    ['location' => 'Toronto, Canada', 'count' => 120],
                    ['location' => 'Sydney, Australia', 'count' => 70]
                ],
                'click_map' => [
                    ['link' => 'https://example.com/weekly-highlights', 'clicks' => 80],
                    ['link' => 'https://example.com/trending-topics', 'clicks' => 70],
                    ['link' => 'https://example.com/community-posts', 'clicks' => 60]
                ]
            ],
            [
                'id' => 5,
                'subject' => 'New Feature Announcement',
                'sent_at' => '1 day ago',
                'opens' => 410,
                'clicks' => 85,
                'delivered' => 950,
                'bounced' => 10,
                'spam' => 1,
                'click_rate' => 20.7,
                'open_rate' => 43.2,
                'engagement_time' => '2m 10s',
                'top_locations' => [
                    ['location' => 'Lagos, Nigeria', 'count' => 160],
                    ['location' => 'Abuja, Nigeria', 'count' => 120],
                    ['location' => 'Johannesburg, SA', 'count' => 80],
                    ['location' => 'Dubai, UAE', 'count' => 50]
                ],
                'click_map' => [
                    ['link' => 'https://example.com/new-features', 'clicks' => 35],
                    ['link' => 'https://example.com/tutorial', 'clicks' => 30],
                    ['link' => 'https://example.com/feedback', 'clicks' => 20]
                ]
            ]
        ];

        // Recent clicks
        $recentClicks = [
            [
                'id' => 1,
                'user' => 'John Doe',
                'link' => 'https://example.com/event-registration',
                'clicked_at' => '15 minutes ago',
                'source' => 'Email'
            ],
            [
                'id' => 2,
                'user' => 'Jane Smith',
                'link' => 'https://example.com/newsletter',
                'clicked_at' => '45 minutes ago',
                'source' => 'KingsChat'
            ],
            [
                'id' => 3,
                'user' => 'Bob Wilson',
                'link' => 'https://example.com/updates',
                'clicked_at' => '1 hour ago',
                'source' => 'Email'
            ],
            [
                'id' => 4,
                'user' => 'Alice Johnson',
                'link' => 'https://example.com/profile',
                'clicked_at' => '2 hours ago',
                'source' => 'KingsChat'
            ],
            [
                'id' => 5,
                'user' => 'Charlie Brown',
                'link' => 'https://example.com/settings',
                'clicked_at' => '3 hours ago',
                'source' => 'Email'
            ]
        ];

        return Inertia::render('Dashboard', [
            'analytics' => $analytics,
            'weeklyStats' => $weeklyStats,
            'recentEmails' => $recentEmails,
            'recentClicks' => $recentClicks
        ]);
    }
} 