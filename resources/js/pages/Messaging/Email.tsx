import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Mail, Users, MousePointerClick, AlertCircle, Clock } from 'lucide-react';
import { 
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Card } from '@/components/ui/card';
import { EmailAnalytics } from '@/components/messaging/email-analytics';
import { EmailTable } from '@/components/messaging/email-table';
import { MessageForm } from '@/components/messaging/message-form';
import moment from 'moment';



// Define the form schema with Zod
const formSchema = z.object({
    subject: z.string().min(1, { message: "Subject is required" }),
    message: z.string().min(1, { message: "Message is required" }),
});

type FormValues = z.infer<typeof formSchema>;

interface EmailMessage {
    id: number;
    subject: string;
    message: string;
    sent_at: string;
    delivered_count: number;
    opened_count: number;
    click_count: number;
    status: string;
    filters: {
        designation: string;
        zone: string;
        country: string;
    };
    recipients: Array<{
        id: number;
        email: string;
        status: string;
        delivered_at: string;
        opened_at: string;
        error: string | null;
    }>;
}

interface Props {
    messages: EmailMessage[];
    analytics: {
        total_messages: number;
        total_delivered: number;
        total_opened: number;
        total_clicks: number;
        average_open_rate: number;
        average_click_rate: number;
    };
    filters: {
        designations: string[];
        zones: string[];
        countries: string[];
    };
}

export default function Email({ messages, analytics, filters }: Props) {
    const [isNewEmailDialogOpen, setIsNewEmailDialogOpen] = useState(false);
    const [selectedEmail, setSelectedEmail] = useState<EmailMessage | null>(null);

    const getEmailAnalytics = (email: EmailMessage) => {
        const total = email.recipients?.length || 0;
        const delivered = email.recipients?.filter(r => r.status === 'delivered').length || 0;
        const opened = email.recipients?.filter(r => r.status === 'opened').length || 0;
        const failed = email.recipients?.filter(r => r.status === 'failed').length || 0;
        const pending = email.recipients?.filter(r => r.status === 'pending').length || 0;

        return {
            total_recipients: total,
            delivered,
            opened,
            failed,
            pending,
            delivery_rate: total ? Math.round((delivered / total) * 100) : 0,
            open_rate: delivered ? Math.round((opened / delivered) * 100) : 0,
        };
    };

    return (
        <AppSidebarLayout>
            <div className="p-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold">Email Broadcasts</h1>
                    <Dialog open={isNewEmailDialogOpen} onOpenChange={setIsNewEmailDialogOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Mail className="mr-2 h-4 w-4" />
                                New Email
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-3xl">
                            <DialogHeader>
                                <DialogTitle>Send Email Broadcast</DialogTitle>
                                <DialogDescription>
                                    Broadcast an email to filtered users
                                </DialogDescription>
                            </DialogHeader>
                            <MessageForm 
                                onSubmitSuccess={() => setIsNewEmailDialogOpen(false)}
                                filters={filters}
                                type="email"
                            />
                        </DialogContent>
                    </Dialog>
                </div>

                <EmailAnalytics analytics={analytics} />
                <EmailTable messages={messages}/>

                {selectedEmail && (
                    <Dialog open={!!selectedEmail} onOpenChange={(open) => !open && setSelectedEmail(null)}>
                        <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                            <DialogHeader>
                                <DialogTitle className="text-xl font-bold">{selectedEmail.subject}</DialogTitle>
                                <DialogDescription>
                                    Sent {moment(selectedEmail.sent_at).format('MMMM D, YYYY [at] h:mm A')}
                                </DialogDescription>
                            </DialogHeader>

                            {/* Email Analytics */}
                            <div className="grid gap-4 md:grid-cols-5">
                                <Card className="p-4">
                                    <div className="flex items-center gap-2">
                                        <Users className="h-4 w-4 text-muted-foreground" />
                                        <div className="text-sm font-medium">Recipients</div>
                                    </div>
                                    <div className="text-2xl font-bold mt-2">
                                        {getEmailAnalytics(selectedEmail).total_recipients}
                                    </div>
                                </Card>

                                <Card className="p-4">
                                    <div className="flex items-center gap-2">
                                        <Mail className="h-4 w-4 text-muted-foreground" />
                                        <div className="text-sm font-medium">Delivered</div>
                                    </div>
                                    <div className="text-2xl font-bold mt-2">
                                        {getEmailAnalytics(selectedEmail).delivered}
                                        <span className="text-sm font-normal text-muted-foreground ml-2">
                                            ({getEmailAnalytics(selectedEmail).delivery_rate}%)
                                        </span>
                                    </div>
                                </Card>

                                <Card className="p-4">
                                    <div className="flex items-center gap-2">
                                        <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                                        <div className="text-sm font-medium">Opened</div>
                                    </div>
                                    <div className="text-2xl font-bold mt-2">
                                        {getEmailAnalytics(selectedEmail).opened}
                                        <span className="text-sm font-normal text-muted-foreground ml-2">
                                            ({getEmailAnalytics(selectedEmail).open_rate}%)
                                        </span>
                                    </div>
                                </Card>

                                <Card className="p-4">
                                    <div className="flex items-center gap-2">
                                        <AlertCircle className="h-4 w-4 text-muted-foreground" />
                                        <div className="text-sm font-medium">Failed</div>
                                    </div>
                                    <div className="text-2xl font-bold mt-2">
                                        {getEmailAnalytics(selectedEmail).failed}
                                    </div>
                                </Card>

                                <Card className="p-4">
                                    <div className="flex items-center gap-2">
                                        <Clock className="h-4 w-4 text-muted-foreground" />
                                        <div className="text-sm font-medium">Pending</div>
                                    </div>
                                    <div className="text-2xl font-bold mt-2">
                                        {getEmailAnalytics(selectedEmail).pending}
                                    </div>
                                </Card>
                            </div>

                            {/* Message Content */}
                            <Card className="p-6">
                                <h2 className="text-lg font-semibold mb-4">Message Content</h2>
                                <div className="prose max-w-none" dangerouslySetInnerHTML={{ __html: selectedEmail.message }} />
                            </Card>

                            {/* Filters Applied */}
                            <Card className="p-6">
                                <h2 className="text-lg font-semibold mb-4">Filters Applied</h2>
                                <div className="grid gap-4 md:grid-cols-3">
                                    <div>
                                        <div className="font-medium">Designation</div>
                                        <div className="text-muted-foreground">{selectedEmail.filters.designation}</div>
                                    </div>
                                    <div>
                                        <div className="font-medium">Zone</div>
                                        <div className="text-muted-foreground">{selectedEmail.filters.zone}</div>
                                    </div>
                                    <div>
                                        <div className="font-medium">Country</div>
                                        <div className="text-muted-foreground">{selectedEmail.filters.country}</div>
                                    </div>
                                </div>
                            </Card>
                        </DialogContent>
                    </Dialog>
                )}
            </div>
        </AppSidebarLayout>
    );
} 