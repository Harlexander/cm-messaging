<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\UserList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\KingschatDispatch;
use App\Models\KingschatDispatchRecipient;

class MessagingController extends Controller
{

    public function email()
    {
        // Mock data for email history
        $messages = [
            [
                'id' => 1,
                'subject' => 'Welcome to Our Platform',
                'message' => 'Thank you for joining our platform. We\'re excited to have you as part of our community!',
                'sent_at' => '2024-02-28 10:00:00',
                'delivered_count' => 1500,
                'opened_count' => 1200,
                'click_count' => 450,
            ],
            [
                'id' => 2,
                'subject' => 'March Newsletter',
                'message' => 'Check out our latest updates and upcoming events in this month\'s newsletter.',
                'sent_at' => '2024-02-27 15:30:00',
                'delivered_count' => 1450,
                'opened_count' => 1100,
                'click_count' => 380,
            ],
            [
                'id' => 3,
                'subject' => 'Important Updates',
                'message' => 'We\'ve made some important changes to our platform. Please review them at your earliest convenience.',
                'sent_at' => '2024-02-26 09:15:00',
                'delivered_count' => 1400,
                'opened_count' => 1150,
                'click_count' => 420,
            ],
            [
                'id' => 4,
                'subject' => 'Special Announcement',
                'message' => 'We have some exciting news to share with our community!',
                'sent_at' => '2024-02-25 11:45:00',
                'delivered_count' => 1480,
                'opened_count' => 1250,
                'click_count' => 520,
            ],
            [
                'id' => 5,
                'subject' => 'Weekly Digest',
                'message' => 'Here\'s a summary of this week\'s most important updates and activities.',
                'sent_at' => '2024-02-24 14:20:00',
                'delivered_count' => 1420,
                'opened_count' => 1180,
                'click_count' => 390,
            ],
        ];

        // Calculate analytics
        $total_delivered = array_sum(array_column($messages, 'delivered_count'));
        $total_opened = array_sum(array_column($messages, 'opened_count'));
        $total_clicks = array_sum(array_column($messages, 'click_count'));
        
        $average_open_rate = $total_delivered > 0 
            ? round(($total_opened / $total_delivered) * 100, 1)
            : 0;
            
        $average_click_rate = $total_opened > 0 
            ? round(($total_clicks / $total_opened) * 100, 1)
            : 0;

        $analytics = [
            'total_messages' => count($messages),
            'total_delivered' => $total_delivered,
            'total_opened' => $total_opened,
            'total_clicks' => $total_clicks,
            'average_open_rate' => $average_open_rate,
            'average_click_rate' => $average_click_rate,
        ];

        // Filter options based on the sample data
        $filters = [
            'designations' => [
                'Cell Leader',
                'Church Pastor',
                'Senior Cell Leader',
                'PCF Leader',
                'Zonal Secretary',
                'Bible Study Class Teacher',
                'Outreach Coordinator',
            ],
            'zones' => [
                'CE LAGOS SUBZONE C',
                'CE SOUTH EAST ASIA',
                'CE EWCA ZONE 4',
                'CE SA ZONE 3',
                'CE SOUTH WEST ZONE 3',
                'CE SOUTH EAST ZONE 3',
                'CAMPUS MINISTRY',
                'CE QUEBEC ZONE',
                'CE SOUTH WEST ZONE 4',
                'CE USA 1 ZONE 1',
                'CE MINISTRY CENTER ABUJA',
                'CE SA ZONE 5',
            ],
            'countries' => [
                'Nigeria',
                'Philippines',
                'Gabon',
                'Botswana',
                'United Kingdom',
                'Canada',
                'United States',
                'Zimbabwe',
            ],
        ];

        return Inertia::render('Messaging/Email', [
            'messages' => $messages,
            'analytics' => $analytics,
            'filters' => $filters,
        ]);
    }

    public function users()
    {
        $users = UserList::select([
            'id',
            'full_name',
            'kingschat_handle',
            'kc_user_id',
            'email',
            'designation',
            'zone',
            'country',
            'created_at as joined_date'
        ])->get();

        // Get unique values for filters
        $filters = [
            'designations' => [],
            'zones' => [],
            'countries' => [],
        ];

        return Inertia::render('Messaging/Users', [
            'users' => $users,
            'filters' => $filters
        ]);
    }

    public function sendEmail(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // TODO: Implement email sending logic
            // For now, just log the attempt
            Log::info('Email broadcast attempt', $validated);

            return response()->json([
                'success' => true,
                'message' => 'Email broadcast initiated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email broadcast',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendKingschat(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'designation' => 'required|string',
            'zone' => 'required|string',
            'country' => 'required|string',
        ]);

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Create the dispatch record
            $dispatch = KingschatDispatch::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'filters' => [
                    'designation' => $validated['designation'],
                    'zone' => $validated['zone'],
                    'country' => $validated['country'],
                ],
                'status' => 'pending',
            ]);

            // Query users based on filters
            $query = UserList::query()
                ->whereNotNull('kc_user_id');

            if ($validated['designation'] !== 'all') {
                $query->where('designation', $validated['designation']);
            }
            if ($validated['zone'] !== 'all') {
                $query->where('zone', $validated['zone']);
            }
            if ($validated['country'] !== 'all') {
                $query->where('country', $validated['country']);
            }

            // Create recipient records
            $users = $query->get();
            $recipients = $users->map(fn($user) => [
                'kingschat_dispatch_id' => $dispatch->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert recipients in chunks to avoid memory issues
            foreach ($recipients->chunk(1000) as $chunk) {
                KingschatDispatchRecipient::insert($chunk->toArray());
            }

            // Commit the transaction
            DB::commit();

            // Dispatch the command to process the broadcast
            Artisan::queue('kingschat:broadcast', [
                'dispatch_id' => $dispatch->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'KingsChat broadcast initiated successfully',
                'data' => [
                    'dispatch_id' => $dispatch->id,
                    'recipient_count' => $users->count()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to initiate KingsChat broadcast', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate KingsChat broadcast',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 