<?php

namespace App\Http\Controllers;

use App\Models\KingschatDispatch;
use App\Jobs\SendKingsChatBroadcast;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserList;
use App\Models\KingschatDispatchRecipient;
use App\Models\KcHandle;


class KingsChatController extends Controller
{
    public function kingschat()
    {
        // Mock data for message history
        $messages = KingschatDispatch::all();

        // Calculate analytics
        $total_delivered = KingschatDispatchRecipient::count();
        $total_read = KingschatDispatchRecipient::where('read_at', '!=', null)->count();
        $average_read_rate = $total_delivered > 0 ? $total_read / $total_delivered * 100 : 0;

        $analytics = [
            'total_messages' => count($messages),
            'total_delivered' => $total_delivered,
            'total_read' => $total_read,
            'average_read_rate' => $average_read_rate,
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

        return Inertia::render('Messaging/KingsMessage', [
            'messages' => $messages,
            'analytics' => $analytics,
            'filters' => $filters,
        ]);
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

            // Query users based on filters to get count
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

            $recipientCount = $query->count();

            // Commit the transaction
            DB::commit();

            return back()->with(['success' => 'KingsChat broadcast created successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create KingsChat broadcast', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create KingsChat broadcast',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCredentials(Request $request)
    {
        $validated = $request->validate([
            'access_token' => 'required|string',
            'refresh_token' => 'required|string',
            'handle' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            KcHandle::updateOrCreate(
                ['client_id' => env('KINGSCHAT_CLIENT_ID')],
                [
                    'access_token' => $validated['access_token'],
                    'refresh_token' => $validated['refresh_token'],
                    'handle' => $validated['handle']
                ]
            );

            DB::commit();

            return back()->with(['success' => 'KingsChat credentials updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update KingsChat credentials', [
                'error' => $e->getMessage()
            ]);

            return back()->withErrors(['error' => 'Failed to update KingsChat credentials']);
        }
    }
}
