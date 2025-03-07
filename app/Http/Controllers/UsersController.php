<?php

namespace App\Http\Controllers;

use App\Models\UserList;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function users(Request $request)
    {
        // Get initial paginated data
        $users = $this->getUsersQuery($request)
            ->paginate(50)
            ->withQueryString();

        // Get unique values for filters
        $filters = [
            'designations' => UserList::distinct('designation')->pluck('designation'),
            'zones' => UserList::distinct('zone')->pluck('zone'),
            'countries' => UserList::distinct('country')->pluck('country'),
        ];

        return Inertia::render('Messaging/Users', [
            'users' => $users,
            'filters' => $filters
        ]);
    }

    public function search(Request $request)
    {
        $users = $this->getUsersQuery($request)
            ->paginate($request->input('per_page', 50))
            ->withQueryString();

        return response()->json($users);
    }

    private function getUsersQuery(Request $request)
    {
        $query = UserList::query()
            ->select([
                'id',
                'full_name',
                'kingschat_handle',
                'kc_user_id',
                'email',
                'designation',
                'zone',
                'country',
                'created_at as joined_date'
            ]);

        // Apply search filters
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('kingschat_handle', 'like', "%{$search}%");
            });
        }

        // Apply column filters
        if ($designation = $request->input('designation')) {
            $query->where('designation', $designation);
        }

        if ($zone = $request->input('zone')) {
            $query->where('zone', $zone);
        }

        if ($country = $request->input('country')) {
            $query->where('country', $country);
        }

        // Apply sorting
        $sortField = $request->input('sort_field', 'full_name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection)
        ->distinct('email');

        return $query;
    }
}
