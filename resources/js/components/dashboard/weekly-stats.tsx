import { Card } from '@/components/ui/card';
import { TrendingUp, TrendingDown, Users, Mail, MousePointerClick } from 'lucide-react';

interface WeeklyStatsProps {
    stats: {
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
    };
}

export function WeeklyStats({ stats }: WeeklyStatsProps) {
    return (
        <div className="grid gap-4 md:grid-cols-4 mb-8">
            <Card className="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-950">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-blue-600 dark:text-blue-300">New Users This Week</p>
                        <h3 className="text-2xl font-bold text-blue-700 dark:text-blue-200">{stats.new_users.count}</h3>
                    </div>
                    <div className="flex items-center">
                        <Users className="h-8 w-8 text-blue-500" />
                        {stats.new_users.trend > 0 ? (
                            <TrendingUp className="h-4 w-4 text-green-500 ml-2" />
                        ) : (
                            <TrendingDown className="h-4 w-4 text-red-500 ml-2" />
                        )}
                    </div>
                </div>
                <p className="text-sm text-blue-600 dark:text-blue-300 mt-2">
                    {Math.abs(stats.new_users.trend)}% from last week
                </p>
            </Card>

            <Card className="p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-950">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-purple-600 dark:text-purple-300">New Subscribers</p>
                        <h3 className="text-2xl font-bold text-purple-700 dark:text-purple-200">{stats.new_subscribers.count}</h3>
                    </div>
                    <div className="flex items-center">
                        <Users className="h-8 w-8 text-purple-500" />
                        {stats.new_subscribers.trend > 0 ? (
                            <TrendingUp className="h-4 w-4 text-green-500 ml-2" />
                        ) : (
                            <TrendingDown className="h-4 w-4 text-red-500 ml-2" />
                        )}
                    </div>
                </div>
                <p className="text-sm text-purple-600 dark:text-purple-300 mt-2">
                    {Math.abs(stats.new_subscribers.trend)}% from last week
                </p>
            </Card>

            <Card className="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-950">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-green-600 dark:text-green-300">Email Engagement</p>
                        <h3 className="text-2xl font-bold text-green-700 dark:text-green-200">{stats.email_engagement.count}</h3>
                    </div>
                    <div className="flex items-center">
                        <Mail className="h-8 w-8 text-green-500" />
                        {stats.email_engagement.trend > 0 ? (
                            <TrendingUp className="h-4 w-4 text-green-500 ml-2" />
                        ) : (
                            <TrendingDown className="h-4 w-4 text-red-500 ml-2" />
                        )}
                    </div>
                </div>
                <p className="text-sm text-green-600 dark:text-green-300 mt-2">
                    {Math.abs(stats.email_engagement.trend)}% from last week
                </p>
            </Card>

            <Card className="p-4 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900 dark:to-amber-950">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm font-medium text-amber-600 dark:text-amber-300">Click Rate</p>
                        <h3 className="text-2xl font-bold text-amber-700 dark:text-amber-200">{stats.click_rate.count}%</h3>
                    </div>
                    <div className="flex items-center">
                        <MousePointerClick className="h-8 w-8 text-amber-500" />
                        {stats.click_rate.trend > 0 ? (
                            <TrendingUp className="h-4 w-4 text-green-500 ml-2" />
                        ) : (
                            <TrendingDown className="h-4 w-4 text-red-500 ml-2" />
                        )}
                    </div>
                </div>
                <p className="text-sm text-amber-600 dark:text-amber-300 mt-2">
                    {Math.abs(stats.click_rate.trend)}% from last week
                </p>
            </Card>
        </div>
    );
} 