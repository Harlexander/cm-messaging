import { Card } from '@/components/ui/card';
import { Mail, Users, MousePointerClick, BarChart } from 'lucide-react';

interface EmailAnalyticsProps {
    analytics: {
        total_messages: number;
        total_delivered: number;
        total_opened: number;
        total_clicks: number;
        average_open_rate: number;
        average_click_rate: number;
    };
}

export function EmailAnalytics({ analytics }: EmailAnalyticsProps) {
    return (
        <div className="grid gap-4 md:grid-cols-3 lg:grid-cols-5 mb-6">
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <Mail className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Total Emails</div>
                </div>
                <div className="text-2xl font-bold mt-2">{analytics.total_messages}</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Delivered</div>
                </div>
                <div className="text-2xl font-bold mt-2">{analytics.total_delivered}</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <Mail className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Opened</div>
                </div>
                <div className="text-2xl font-bold mt-2">416</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Clicks</div>
                </div>
                <div className="text-2xl font-bold mt-2">160</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <BarChart className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Open Rate</div>
                </div>
                <div className="text-2xl font-bold mt-2">9%</div>
            </Card>
        </div>
    );
} 