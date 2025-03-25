<?php

namespace App\Http\Controllers;

use App\Mail\BroadcastMail;
use App\Models\EmailDispatch;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function index()
    {
        // Get email dispatches with analytics using efficient counting
        $dispatches = EmailDispatch::withCount([
            'recipients',
            'recipients as delivered_count' => function ($query) {
                $query->whereNotNull('delivered_at');
            },
            'recipients as opened_count' => function ($query) {
                $query->whereNotNull('opened_at');
            },
            'recipients as clicked_count' => function ($query) {
                $query->whereNotNull('clicked_at');
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($dispatch) {
            return [
                'id' => $dispatch->id,
                'subject' => $dispatch->subject,
                'message' => $dispatch->message,
                'sent_at' => $dispatch->sent_at,
                'created_at' => $dispatch->created_at,
                'status' => $dispatch->status,
                'total_recipients' => $dispatch->recipients_count,
                'delivered_count' => $dispatch->delivered_count,
                'opened_count' => $dispatch->opened_count,
                'clicked_count' => $dispatch->clicked_count,
                'open_rate' => $dispatch->delivered_count > 0 
                    ? round(($dispatch->opened_count / $dispatch->delivered_count) * 100, 2) 
                    : 0,
            ];
        });

        // Calculate analytics
        $totalDispatches = $dispatches->count();
        $totalDelivered = $dispatches->sum('delivered_count');
        $totalOpened = $dispatches->sum('opened_count');
        $totalClicks = $dispatches->sum('clicked_count');
        $averageOpenRate = $totalDelivered > 0 ? round(($totalOpened / $totalDelivered) * 100, 2) : 0;

        // Get filter options
        $filters = [
            'designations' => UserList::distinct('designation')->pluck('designation'),
            'zones' => UserList::distinct('zone')->pluck('zone'),
            'countries' => UserList::distinct('country')->pluck('country'),
        ];

        return Inertia::render('Messaging/Email', [
            'messages' => $dispatches,
            'analytics' => [
                'total_messages' => $totalDispatches,
                'total_delivered' => $totalDelivered,
                'total_opened' => $totalOpened,
                'total_clicks' => $totalClicks,
                'average_open_rate' => $averageOpenRate,
            ],
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'designation' => 'required|string',
            'zone' => 'required|string',
            'country' => 'required|string',
            'attachment' => 'nullable|file|max:10240', // Max 10MB
        ]);

        try {
            DB::beginTransaction();

            // Handle file upload if present
            $attachmentPath = null;
            $attachmentName = null;
            
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentName = $file->getClientOriginalName();
                $attachmentPath = $file->store('email-attachments', 'public');
            }

            // Create the dispatch record
            $dispatch = EmailDispatch::create([
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'filters' => [
                    'designation' => $validated['designation'],
                    'zone' => $validated['zone'],
                    'country' => $validated['country'],
                ],
                'status' => 'pending',
                'attachment_path' => $attachmentPath ? storage_path('app/public/' . $attachmentPath) : null,
                'attachment_name' => $attachmentName,
            ]);

            // Get count of users who will receive this email
            $query = UserList::query()
                ->whereNotNull('email');

            if ($validated['designation'] !== 'all') {
                $query->where('designation', $validated['designation']);
            }
            if ($validated['zone'] !== 'all') {
                $query->where('zone', $validated['zone']);
            }
            if ($validated['country'] !== 'all') {
                $query->where('country', $validated['country']);
            }

            $recipientCount = $query->count();
            
            DB::commit();

            return back()->with([
                'success' => "Email broadcast created successfully. Will be sent to {$recipientCount} recipients."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create email broadcast', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return back()->withErrors([
                'error' => 'Failed to create email broadcast: ' . $e->getMessage()
            ]);
        }
    }

    public function show(EmailDispatch $dispatch)
    {
        $dispatch->load('recipients');

        $analytics = [
            'total_recipients' => $dispatch->recipients->count(),
            'delivered' => $dispatch->recipients->whereNotNull('delivered_at')->count(),
            'opened' => $dispatch->recipients->whereNotNull('opened_at')->count(),
            'failed' => $dispatch->recipients->where('status', 'failed')->count(),
            'pending' => $dispatch->recipients->where('status', 'pending')->count(),
        ];

        $analytics['delivery_rate'] = $analytics['total_recipients'] > 0 
            ? round(($analytics['delivered'] / $analytics['total_recipients']) * 100, 2)
            : 0;

        $analytics['open_rate'] = $analytics['delivered'] > 0
            ? round(($analytics['opened'] / $analytics['delivered']) * 100, 2)
            : 0;

        return Inertia::render('Messaging/EmailDetails', [
            'dispatch' => $dispatch,
            'analytics' => $analytics,
        ]);
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string',
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        try {
            // Handle file upload if present
            $attachmentPath = null;
            $attachmentName = null;
            
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentName = $file->getClientOriginalName();
                $attachmentPath = $file->store('email-attachments', 'public');
            }

            // Send test email
            Mail::to($validated['email'])
                ->send(new BroadcastMail(
                    [
                        'subject' => $validated['subject'],
                        'message' => $validated['message'],
                        'name' => 'Test User'
                    ],
                    $attachmentPath ? storage_path('app/public/' . $attachmentPath) : null,
                    $attachmentName
                ));

            return back()->with([
                'success' => 'Test email sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json(
                ['error' => 'Failed to send test email: ' . $e->getMessage()],
                500
            );
        }
    }
} 