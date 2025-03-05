import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Mail, MousePointerClick } from 'lucide-react';
import { EmailAnalytics } from './email-analytics';

interface RecentEmail {
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
}

interface RecentClick {
    id: number;
    user: string;
    link: string;
    clicked_at: string;
    source: string;
}

interface RecentActivityProps {
    recentEmails: RecentEmail[];
    recentClicks: RecentClick[];
}

export function RecentActivity({ recentEmails, recentClicks }: RecentActivityProps) {
    const [selectedEmail, setSelectedEmail] = useState<RecentEmail | null>(null);

    return (
        <>
            <div className="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Recent Emails</CardTitle>
                        <Mail className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Subject</TableHead>
                                    <TableHead>Sent</TableHead>
                                    <TableHead>Opens</TableHead>
                                    <TableHead>Clicks</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recentEmails.map((email) => (
                                    <TableRow 
                                        key={email.id}
                                        className="cursor-pointer hover:bg-muted/50"
                                        onClick={() => setSelectedEmail(email)}
                                    >
                                        <TableCell className="font-medium max-w-[200px] truncate">
                                            {email.subject}
                                        </TableCell>
                                        <TableCell>{email.sent_at}</TableCell>
                                        <TableCell>{email.opens}</TableCell>
                                        <TableCell>{email.clicks}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">Recent Link Clicks</CardTitle>
                        <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>User</TableHead>
                                    <TableHead>Link</TableHead>
                                    <TableHead>Time</TableHead>
                                    <TableHead>Source</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recentClicks.map((click) => (
                                    <TableRow key={click.id}>
                                        <TableCell className="font-medium">{click.user}</TableCell>
                                        <TableCell className="max-w-[200px] truncate">
                                            {click.link}
                                        </TableCell>
                                        <TableCell>{click.clicked_at}</TableCell>
                                        <TableCell>{click.source}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            {selectedEmail && (
                <EmailAnalytics
                    email={selectedEmail}
                    open={!!selectedEmail}
                    onOpenChange={(open) => !open && setSelectedEmail(null)}
                />
            )}
        </>
    );
} 