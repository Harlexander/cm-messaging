import { useState } from 'react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { MessageSquare } from 'lucide-react';
import { 
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { MessageAnalytics } from '@/components/messaging/message-analytics';
import { MessageTable } from '@/components/messaging/message-table';
import { MessageForm } from '@/components/messaging/message-form';
import { authenticationTokenResponseI } from 'kingschat-web-sdk/dist/ts/interfaces';
import kingsChatWebSdk from 'kingschat-web-sdk';
import { router } from '@inertiajs/react';
import 'kingschat-web-sdk/dist/stylesheets/style.min.css';
import { toast } from 'sonner';


interface Message {
    id: number;
    title: string;
    message: string;
    sent_at: string;
    delivered_count: number;
    read_count: number;
}

interface Props {
    messages: Message[];
    analytics: {
        total_messages: number;
        total_delivered: number;
        total_read: number;
        average_read_rate: number;
    };
    filters: {
        designations: string[];
        zones: string[];
        countries: string[];
    };
}

export default function KingsMessage({ messages, analytics, filters }: Props) {
    const [isDialogOpen, setIsDialogOpen] = useState(false);
    const [loginData, setLoginData] = useState<authenticationTokenResponseI | null>(null);

    const login = async () => {
        try {
            const handle = prompt('Enter your KingsChat handle');
            if (!handle) {
                toast.error('Handle is required');
                return;
            }
            const result = await kingsChatWebSdk.login({
                clientId: "3d6ff64c-7b41-4b8d-a92c-bb51df27222e",
                scopes: ['send_chat_message'],
            });

            setLoginData(result);

            // Update credentials in backend using Inertia's router
            router.post('/messaging/kingschat/credentials', {
                access_token: result.accessToken,
                refresh_token: result.refreshToken,
                handle: handle,
            }, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('KingsChat credentials updated successfully');
                },
                onError: (errors) => {
                    console.error('Failed to update KingsChat credentials:', errors);
                }
            });
        } catch (error) {
            console.error('Failed to login to KingsChat:', error);
        }
    }

    return (
        <AppSidebarLayout>
            <div className="p-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold">KingsChat Messages</h1>
                    <div className='flex gap-2 items-center'>
                        <a className="kc-web-sdk-btn-m" onClick={login} />
                        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
                            <DialogTrigger asChild>
                                <Button>
                                    <MessageSquare className="mr-2 h-4 w-4" />
                                    New Message
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-3xl">
                                <DialogHeader>
                                    <DialogTitle>Send KingsChat Message</DialogTitle>
                                    <DialogDescription>
                                        Broadcast a message to filtered users via KingsChat
                                    </DialogDescription>
                                </DialogHeader>
                                <MessageForm 
                                    onSubmitSuccess={() => setIsDialogOpen(false)}
                                    filters={filters}
                                />
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <MessageAnalytics analytics={analytics} />
                <MessageTable messages={messages} />
            </div>
        </AppSidebarLayout>
    );
} 