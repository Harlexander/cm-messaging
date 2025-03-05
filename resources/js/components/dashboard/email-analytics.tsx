import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Mail, Users, MousePointerClick, Clock, BarChart } from 'lucide-react';

interface EmailAnalyticsProps {
    email: {
        id: number;
        subject: string;
        sent_at: string;
        opens: number;
        clicks: number;
        delivered: number;
        bounced: number;
        spam: number;
        click_rate: number;
        open_rate: number;
        engagement_time: string;
        top_locations: Array<{ location: string; count: number }>;
        click_map: Array<{ link: string; clicks: number }>;
    };
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function EmailAnalytics({ email, open, onOpenChange }: EmailAnalyticsProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-4xl">
                <DialogHeader>
                    <DialogTitle className="text-xl font-bold">{email.subject}</DialogTitle>
                    <p className="text-sm text-muted-foreground">Sent {email.sent_at}</p>
                </DialogHeader>

                <div className="grid gap-4 md:grid-cols-4 mb-6">
                    <Card className="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-950">
                        <div className="flex items-center gap-2">
                            <Mail className="h-4 w-4 text-blue-500" />
                            <div className="text-sm font-medium text-blue-600 dark:text-blue-300">Delivered</div>
                        </div>
                        <div className="text-2xl font-bold text-blue-700 dark:text-blue-200 mt-2">
                            {email.delivered}
                        </div>
                    </Card>

                    <Card className="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-950">
                        <div className="flex items-center gap-2">
                            <Users className="h-4 w-4 text-green-500" />
                            <div className="text-sm font-medium text-green-600 dark:text-green-300">Opens</div>
                        </div>
                        <div className="text-2xl font-bold text-green-700 dark:text-green-200 mt-2">
                            {email.opens} ({email.open_rate}%)
                        </div>
                    </Card>

                    <Card className="p-4 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900 dark:to-amber-950">
                        <div className="flex items-center gap-2">
                            <MousePointerClick className="h-4 w-4 text-amber-500" />
                            <div className="text-sm font-medium text-amber-600 dark:text-amber-300">Clicks</div>
                        </div>
                        <div className="text-2xl font-bold text-amber-700 dark:text-amber-200 mt-2">
                            {email.clicks} ({email.click_rate}%)
                        </div>
                    </Card>

                    <Card className="p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-950">
                        <div className="flex items-center gap-2">
                            <Clock className="h-4 w-4 text-purple-500" />
                            <div className="text-sm font-medium text-purple-600 dark:text-purple-300">Avg. Time</div>
                        </div>
                        <div className="text-2xl font-bold text-purple-700 dark:text-purple-200 mt-2">
                            {email.engagement_time}
                        </div>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card className="p-4">
                        <div className="flex items-center gap-2 mb-4">
                            <BarChart className="h-4 w-4 text-muted-foreground" />
                            <h3 className="font-semibold">Top Locations</h3>
                        </div>
                        <div className="space-y-4">
                            {email.top_locations.map((location, index) => (
                                <div key={index} className="flex items-center justify-between">
                                    <span className="text-sm">{location.location}</span>
                                    <span className="text-sm font-medium">{location.count} opens</span>
                                </div>
                            ))}
                        </div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-2 mb-4">
                            <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                            <h3 className="font-semibold">Link Clicks</h3>
                        </div>
                        <div className="space-y-4">
                            {email.click_map.map((click, index) => (
                                <div key={index} className="flex items-center justify-between">
                                    <span className="text-sm max-w-[200px] truncate">{click.link}</span>
                                    <span className="text-sm font-medium">{click.clicks} clicks</span>
                                </div>
                            ))}
                        </div>
                    </Card>
                </div>
            </DialogContent>
        </Dialog>
    );
} 