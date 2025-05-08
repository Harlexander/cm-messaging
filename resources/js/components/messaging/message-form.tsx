import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Form,
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Loader2 } from 'lucide-react';
import { toast } from 'sonner';
import TextEditor, { TextEditor2 } from './text-editor';
import { Info } from 'lucide-react';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/ui/tooltip";

const formSchema = z.object({
    title: z.string().optional(),
    subject: z.string().optional(),
    message: z.string().min(1, { message: "Message is required" }),
    designation: z.string().default("all"),
    zone: z.string().default("all"),
    country: z.string().default("all"),
    attachment: z.any().optional(),
    test_email: z.string().optional(),
    banner_image: z.any().optional(),
}).refine(data => {
    // Ensure either title (for KingsChat) or subject (for email) is provided
    return data.title || data.subject;
}, {
    message: "Either title or subject is required",
    path: ['title'], // This will show the error on the title field
});

type FormValues = z.infer<typeof formSchema>;

interface MessageFormProps {
    onSubmitSuccess: () => void;
    filters: {
        designations: string[];
        zones: string[];
        countries: string[];
    };
    type?: 'email' | 'kingschat';
}

export function MessageForm({ onSubmitSuccess, filters, type = 'kingschat' }: MessageFormProps) {
    const form = useForm<FormValues>({
        resolver: zodResolver(formSchema),
        defaultValues: {
            title: '',
            subject: '',
            message: '',
            designation: 'all',
            zone: 'all',
            country: 'all',
            attachment: undefined,
            test_email: '',
            banner_image: undefined,
        },
    });

    const onSubmit = (data: FormValues) => {
        if(!confirm('Are you sure you want to send this message?')) return;

        const endpoint = type === 'email' 
            ? '/messaging/email/broadcast' 
            : '/messaging/kingschat/broadcast';

        const payload = type === 'email' 
            ? { 
                title: data.title,
                subject: data.subject, 
                message: data.message, 
                designation: data.designation, 
                zone: data.zone, 
                country: data.country,
                attachment: data.attachment,
                banner_image: data.banner_image,
            }
            : { 
                title: data.title, 
                message: data.message, 
                designation: data.designation, 
                zone: data.zone, 
                country: data.country 
            };

        router.post(endpoint, payload, {
            onSuccess: () => {
                toast.success('Messages queued successfully!');
                onSubmitSuccess();
                form.reset();
            },
            onError: () => {
                toast.error('Failed to send message');
            }
        });
    };

    const sendTestEmail = () => {
        const testEmail = form.getValues('test_email');
        if (!testEmail) {
            toast.error('Please enter a test email address');
            return;
        }

        const data = form.getValues();
        router.post(route('email.test'), {
            email: testEmail,
            subject: data.subject,
            message: data.message,
            attachment: data.attachment,
            banner_image: data.banner_image,
            title: data.title,
        }, {
            onSuccess: () => {
                toast.success('Test email sent successfully!');
            },
            onError: () => {
                toast.error('Failed to send test email');
            }
        });
    };

    return (
        <Form {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                <div className="grid grid-cols-3 gap-4">
                    <FormField
                        control={form.control}
                        name="designation"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>Designation</FormLabel>
                                <Select 
                                    onValueChange={field.onChange} 
                                    defaultValue={field.value}
                                >
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select designation" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        {filters.designations.map((designation) => (
                                            designation && (
                                                <SelectItem key={designation} value={designation}>
                                                    {designation}
                                                </SelectItem>
                                            )
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormItem>
                        )}
                    />
                    <FormField
                        control={form.control}
                        name="zone"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>Zone</FormLabel>
                                <Select 
                                    onValueChange={field.onChange} 
                                    defaultValue={field.value}
                                >
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select zone" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        {filters.zones.map((zone) => (
                                            zone && (
                                                <SelectItem key={zone} value={zone}>
                                                    {zone}
                                                </SelectItem>
                                            )
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormItem>
                        )}
                    />
                    <FormField
                        control={form.control}
                        name="country"
                        render={({ field }) => (
                            <FormItem>
                                <FormLabel>Country</FormLabel>
                                <Select 
                                    onValueChange={field.onChange} 
                                    defaultValue={field.value}
                                >
                                    <FormControl>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select country" />
                                        </SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        <SelectItem value="all">All</SelectItem>
                                        {filters.countries.map((country) => (
                                            country && (
                                                <SelectItem key={country} value={country}>
                                                    {country}
                                                </SelectItem>
                                            )
                                        ))}
                                    </SelectContent>
                                </Select>
                            </FormItem>
                        )}
                    />
                </div>

                <FormField
                    control={form.control}
                    name={'title'}
                    render={({ field }) => (
                        <FormItem>
                            <FormLabel>{'Title'}</FormLabel>
                            <FormControl>
                                <Input
                                    placeholder={type === 'email' ? "Sender title e.g Cell Ministry, One billion outreaches" : "Enter message title"}
                                    {...field}
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    )}
                />

                {
                    type === 'email' && (
                        <FormField
                            control={form.control}
                            name={'subject'}
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>{'Subject'}</FormLabel>
                                    <FormControl>
                                        <Input
                                            placeholder={'Enter email subject'}
                                            {...field}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    )
                }
                
                {
                    type == 'email' ? (
                        <TextEditor2 setMessage={(message) => form.setValue('message', message)} />
                    ) : (
                        <Textarea
                            placeholder="Enter message"
                            {...form.register('message')}
                        />
                    )
                }

                {type === 'email' && (
                    <>
                        <div className="space-y-4">
                            <div className="flex items-center gap-2">
                                <FormLabel>Available User Fields</FormLabel>
                                <TooltipProvider>
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Info className="h-4 w-4 text-muted-foreground" />
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>Use these placeholders in your message:</p>
                                            <ul className="list-disc list-inside mt-2">
                                                <li>{"{{ user.full_name }}"}</li>
                                                <li>{"{{ user.email }}"}</li>
                                                <li>{"{{ user.phone }}"}</li>
                                                <li>{"{{ user.designation }}"}</li>
                                                <li>{"{{ user.zone }}"}</li>
                                                <li>{"{{ user.country }}"}</li>
                                            </ul>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </div>

                            <FormField
                                control={form.control}
                                name="test_email"
                                render={({ field }) => (
                                    <FormItem>
                                        <FormLabel>Test Email Address</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="email"
                                                placeholder="Enter email for testing"
                                                {...field}
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                )}
                            />
                        </div>

                        <FormField
                            control={form.control}
                            name="attachment"
                            render={({ field: { value, onChange, ...field } }) => (
                                <FormItem>
                                    <FormLabel>Attachment</FormLabel>
                                    <FormControl>
                                        <Input 
                                            type="file"
                                            onChange={(e) => {
                                                const file = e.target.files?.[0];
                                                if (file) {
                                                    onChange(file);
                                                }
                                            }}
                                            {...field}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />

                        <FormField
                            control={form.control}
                            name="banner_image"
                            render={({ field }) => (
                                <FormItem>
                                    <FormLabel>Banner Image</FormLabel>
                                    <FormControl>
                                        <Input 
                                            type="file"
                                            onChange={(e) => {
                                                const file = e.target.files?.[0];
                                                if (file) {
                                                    field.onChange(file);
                                                }
                                            }}
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            )}
                        />
                    </>
                )}

                <div className='flex gap-4'>
                {
                    type === 'email' && (
                        <Button
                            type="button"
                            variant="secondary"
                            className="w-full"
                            onClick={sendTestEmail}
                        >
                            Send Test Email
                        </Button>
                    )
                }
                <Button
                    type="submit"
                    className="w-full"
                    disabled={form.formState.isSubmitting}
                >
                    {form.formState.isSubmitting ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Sending...
                        </>
                    ) : (
                        'Send Broadcast'
                    )}
                </Button>
                </div>
            </form>
        </Form>
    );
} 