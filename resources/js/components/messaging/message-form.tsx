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
import TextEditor from './text-editor';

const formSchema = z.object({
    title: z.string().optional(),
    subject: z.string().optional(),
    message: z.string().min(1, { message: "Message is required" }),
    designation: z.string().default("all"),
    zone: z.string().default("all"),
    country: z.string().default("all"),
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
        },
    });

    const onSubmit = (data: FormValues) => {
        const endpoint = type === 'email' 
            ? '/messaging/email/broadcast' 
            : '/messaging/kingschat/broadcast';

        const payload = type === 'email' 
            ? { 
                subject: data.subject, 
                message: data.message, 
                designation: data.designation, 
                zone: data.zone, 
                country: data.country 
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
                    name={type === 'email' ? 'subject' : 'title'}
                    render={({ field }) => (
                        <FormItem>
                            <FormLabel>{type === 'email' ? 'Subject' : 'Title'}</FormLabel>
                            <FormControl>
                                <Input
                                    placeholder={type === 'email' ? "Enter email subject" : "Enter message title"}
                                    {...field}
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    )}
                />

                <TextEditor setMessage={(message) => form.setValue('message', message)} />

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
            </form>
        </Form>
    );
} 