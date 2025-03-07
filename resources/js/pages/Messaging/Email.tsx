import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { router } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Mail } from 'lucide-react';
import { 
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { EmailAnalytics } from '@/components/messaging/email-analytics';
import { EmailTable } from '@/components/messaging/email-table';
import { MessageForm } from '@/components/messaging/message-form';



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
            </div>
        </AppSidebarLayout>
    );
} 