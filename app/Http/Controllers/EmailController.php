<?php

namespace App\Http\Controllers;

use App\Mail\BroadcastMail;
use App\Models\EmailDispatch;
use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class EmailController extends Controller
{
    public function index()
    {
        // Get email dispatches with analytics
        $dispatches = EmailDispatch::with('recipients')
            ->get()
            ->map(function ($dispatch) {
                $totalRecipients = $dispatch->recipients()->count();
                $delivered = $dispatch->recipients()->where('status', 'delivered')->count();
                $opened = $dispatch->recipients()->where('status', 'opened')->count();

                return [
                    'id' => $dispatch->id,
                    'subject' => $dispatch->subject,
                    'message' => $dispatch->message,
                    'sent_at' => $dispatch->sent_at,
                    'status' => $dispatch->status,
                    'total_recipients' => $totalRecipients,
                    'delivered_count' => $delivered,
                    'opened_count' => $opened,
                    'open_rate' => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0,
                ];
            });

        // Calculate analytics
        $totalDispatches = $dispatches->count();
        $totalDelivered = $dispatches->sum('delivered_count');
        $totalOpened = $dispatches->sum('opened_count');
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
        ]);

        try {
            DB::beginTransaction();

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
            'delivered' => $dispatch->recipients->where('status', 'delivered')->count(),
            'opened' => $dispatch->recipients->where('status', 'opened')->count(),
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
} 