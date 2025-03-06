import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card } from '@/components/ui/card';
import { Mail, Users, MousePointerClick, AlertCircle, Clock } from 'lucide-react';
import moment from 'moment';

interface EmailDispatch {
    id: number;
    subject: string;
    message: string;
    status: string;
    sent_at: string;
    completed_at: string;
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
    dispatch: EmailDispatch;
    analytics: {
        total_recipients: number;
        delivered: number;
        opened: number;
        failed: number;
        pending: number;
        delivery_rate: number;
        open_rate: number;
    };
}

export default function EmailDetails({ dispatch, analytics }: Props) {
    return (
        <AppSidebarLayout>
            <div className="p-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold mb-2">{dispatch.subject}</h1>
                    <p className="text-muted-foreground">
                        Sent {moment(dispatch.sent_at).format('MMMM D, YYYY [at] h:mm A')}
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-5 mb-6">
                    <Card className="p-4">
                        <div className="flex items-center gap-2">
                            <Users className="h-4 w-4 text-muted-foreground" />
                            <div className="text-sm font-medium">Recipients</div>
                        </div>
                        <div className="text-2xl font-bold mt-2">{analytics.total_recipients}</div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-2">
                            <Mail className="h-4 w-4 text-muted-foreground" />
                            <div className="text-sm font-medium">Delivered</div>
                        </div>
                        <div className="text-2xl font-bold mt-2">
                            {analytics.delivered}
                            <span className="text-sm font-normal text-muted-foreground ml-2">
                                ({analytics.delivery_rate}%)
                            </span>
                        </div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-2">
                            <MousePointerClick className="h-4 w-4 text-muted-foreground" />
                            <div className="text-sm font-medium">Opened</div>
                        </div>
                        <div className="text-2xl font-bold mt-2">
                            {analytics.opened}
                            <span className="text-sm font-normal text-muted-foreground ml-2">
                                ({analytics.open_rate}%)
                            </span>
                        </div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-2">
                            <AlertCircle className="h-4 w-4 text-muted-foreground" />
                            <div className="text-sm font-medium">Failed</div>
                        </div>
                        <div className="text-2xl font-bold mt-2">{analytics.failed}</div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-2">
                            <Clock className="h-4 w-4 text-muted-foreground" />
                            <div className="text-sm font-medium">Pending</div>
                        </div>
                        <div className="text-2xl font-bold mt-2">{analytics.pending}</div>
                    </Card>
                </div>

                <Card className="p-6">
                    <h2 className="text-lg font-semibold mb-4">Message Content</h2>
                    <div className="prose max-w-none" dangerouslySetInnerHTML={{ __html: dispatch.message }} />
                </Card>

                <Card className="mt-6 p-6">
                    <h2 className="text-lg font-semibold mb-4">Filters Applied</h2>
                    <div className="grid gap-4 md:grid-cols-3">
                        <div>
                            <div className="font-medium">Designation</div>
                            <div className="text-muted-foreground">{dispatch.filters.designation}</div>
                        </div>
                        <div>
                            <div className="font-medium">Zone</div>
                            <div className="text-muted-foreground">{dispatch.filters.zone}</div>
                        </div>
                        <div>
                            <div className="font-medium">Country</div>
                            <div className="text-muted-foreground">{dispatch.filters.country}</div>
                        </div>
                    </div>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 