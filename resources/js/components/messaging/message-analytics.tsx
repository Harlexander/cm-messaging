import { Card } from '@/components/ui/card';
import { MessageSquare, Users } from 'lucide-react';

interface MessageAnalyticsProps {
    analytics: {
        total_messages: number;
        total_delivered: number;
        total_read: number;
        average_read_rate: number;
    };
}

export function MessageAnalytics({ analytics }: MessageAnalyticsProps) {
    return (
        <div className="grid gap-4 md:grid-cols-4 mb-6">
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <MessageSquare className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Total Messages</div>
                </div>
                <div className="text-2xl font-bold mt-2">{analytics.total_messages}</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Total Delivered</div>
                </div>
                <div className="text-2xl font-bold mt-2">{analytics.total_delivered}</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Total Read</div>
                </div>
                <div className="text-2xl font-bold mt-2">{analytics.total_read}</div>
            </Card>
            <Card className="p-4">
                <div className="flex items-center gap-2">
                    <Users className="h-4 w-4 text-muted-foreground" />
                    <div className="text-sm font-medium">Average Read Rate</div>
                </div>
                <div className="text-2xl font-bold mt-2">{analytics.average_read_rate}%</div>
            </Card>
        </div>
    );
} 