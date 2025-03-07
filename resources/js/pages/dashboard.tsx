import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Mail, MessageSquare, Users, MousePointerClick } from 'lucide-react';
import { WeeklyStats } from '@/components/dashboard/weekly-stats';
import { RecentActivity } from '@/components/dashboard/recent-activity';

interface Analytics {
    emails: {
        total: number;
        growth: number;
    };
    kingschat: {
        total: number;
        growth: number;
    };
    users: {
        total: number;
        growth: number;
    };
    clicks: {
        total: number;
        growth: number;
    };
}

interface WeeklyStats {
    new_users: {
        count: number;
        trend: number;
    };
    new_subscribers: {
        count: number;
        trend: number;
    };
    email_engagement: {
        count: number;
        trend: number;
    };
    click_rate: {
        count: number;
        trend: number;
    };
}

interface RecentEmail {
    id: number;
    subject: string;
    sent_at: string;
    opens: number;
    clicks: number;
}

interface RecentClick {
    id: number;
    user: string;
    link: string;
    clicked_at: string;
    source: string;
}

interface Props {
    analytics: Analytics;
    weeklyStats: WeeklyStats;
    recentEmails: RecentEmail[];
    recentClicks: RecentClick[];
}

export default function Dashboard({ analytics, weeklyStats, recentEmails, recentClicks }: Props) {
    console.log({weeklyStats, recentClicks, recentEmails});

    return (        
        <AppSidebarLayout>
            <div className="p-6">
                <h1 className="text-2xl font-bold mb-6">Messaging Dashboard</h1>
                
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Emails Sent</CardTitle>
                            <Mail className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{analytics.emails.total.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{analytics.emails.growth}% from last month
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">KingsChat Messages</CardTitle>
                            <MessageSquare className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{analytics.kingschat.total.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{analytics.kingschat.growth}% from last month
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{analytics.users.total.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{analytics.users.growth}% from last month
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Link Clicks</CardTitle>
                            <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{analytics.clicks.total.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground">
                                +{analytics.clicks.growth}% from last month
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <WeeklyStats stats={weeklyStats} />
                <RecentActivity recentEmails={recentEmails} recentClicks={recentClicks} />
            </div>
        </AppSidebarLayout>
    );
}
