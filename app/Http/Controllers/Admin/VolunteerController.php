<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersionNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class VolunteerController extends Controller
{
    public function index()
    {
        //     $notes = VersionNote::orderBy('date', 'desc')->get();
        //    return view('admin.version', compact('notes'));
        return view('admin.volunteer_approved_name_add');
    }
    public function volunteer_group_signUp()
    {
        $volunteer_users = DB::table('volunteer_group_homes_users')
            ->select('id', 'first_name', 'last_name')
            ->get();

        // Format the data for the view
        $users = $volunteer_users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ];
        });

        return view('admin.volunteer_group_home_signUp', compact('users'));
    }

    public function volunteer_checkIn(Request $request)
    {
        if ($request->ajax()) {
            // Handle dashboard counts AJAX request FIRST
            if ($request->has('get_counts')) {
                // Get same data as DataTable
                $rows = DB::table('volunteer_group_homes_users')
                    ->select('id', 'first_name', 'start_time', 'end_time')
                    ->whereNotNull('start_time')
                    ->whereNotNull('end_time')
                    ->where('is_active', 1)
                    ->get();

                // Count total volunteers
                $totalVolunteers = $rows->count();

                // Calculate total hours from all durations
                $totalHours = 0;
                foreach ($rows as $item) {
                    $start = Carbon::parse($item->start_time);
                    $end = Carbon::parse($item->end_time);
                    $totalHours += $start->diffInHours($end);
                }

                return response()->json([
                    'totalVolunteers' => $totalVolunteers,
                    'totalHours' => $totalHours
                ]);
            }

            // Handle DataTable AJAX request
            $rows = DB::table('volunteer_group_homes_users')
                ->select('id', 'first_name', 'start_time', 'end_time')
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->where('is_active', 1)
                ->get();

            // Map duration
            $data = $rows->map(function ($item) {
                $start = Carbon::parse($item->start_time);
                $end = Carbon::parse($item->end_time);
                $duration = $start->diff($end)->format('%H:%I:%S');

                return [
                    'id' => $item->id,
                    'first_name' => $item->first_name,
                    'start_time' => $item->start_time,
                    'end_time' => $item->end_time,
                    'duration' => $duration,
                ];
            });

            return datatables()->of($data)->make(true);
        }

        // Send all volunteers (for dropdown)
        $volunteers = DB::table('volunteer_group_homes_users')
            ->select('id', DB::raw("CONCAT(first_name, ' ', last_name) AS name"))
            ->where('is_active', 1)
            ->whereNull('start_time')
            ->whereNull('end_time')
            ->get();

        return view('admin.volunteer_check_in', compact('volunteers'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'volunteer_id' => 'required|exists:volunteer_group_homes_users,id',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        DB::table('volunteer_group_homes_users')
            ->where('id', $request->volunteer_id)
            ->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_active' => 1,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Volunteer check-in updated successfully']);
    }

    public function edit($id)
    {
        $volunteer = DB::table('volunteer_group_homes_users')->find($id);
        return response()->json($volunteer);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Step 1: Find if any record (excluding the current one) has the same start and end time
        $existingRecord = DB::table('volunteer_group_homes_users')
            ->where('start_time', $request->start_time)
            ->where('end_time', $request->end_time)
            ->where('id', '=', $id)
            ->first();

        // Step 2: If such record exists, clear its start_time and end_time
        if ($existingRecord) {
            DB::table('volunteer_group_homes_users')
                ->where('id', $existingRecord->id)
                ->update([
                    'start_time' => null,
                    'end_time' => null,
                    'updated_at' => now(),
                ]);
        }

        // Step 3: Assign new start_time and end_time to current record
        DB::table('volunteer_group_homes_users')
            ->where('id', $request->volunteer_id)
            ->update([
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Volunteer time reassigned successfully']);
    }

    public function destroy($id)
    {
        DB::table('volunteer_group_homes_users')
            ->where('id', $id)
            ->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Volunteer soft-deleted successfully']);
    }
    public function assign_case_number_delete(Request $request)
    {

        $caseNumbers = $request->input('case_numbers');
        $caseNumbers = json_decode($caseNumbers, true);

        if (empty($caseNumbers)) {
            return response()->json(['message' => 'No case numbers provided'], 400);
        }
        foreach ($caseNumbers as $caseNumber) {
            DB::table('users')
                ->where('case_number', $caseNumber)
                ->update(['volunteer_id' => null]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Volunteer Case Numbers deleted successfully'
        ], 200);
    }
   public function assignUp_case_delete(Request $request)
{
    $caseNumbers = $request->input('case_numbers'); // e.g., ["67368", "67403"]

    if (empty($caseNumbers)) {
        return response()->json(['message' => 'No case numbers provided'], 400);
    }

    foreach ($caseNumbers as $caseNumber) {
        // Find the user by case number
         DB::table('users')
                ->where('id', $caseNumber)
                ->update(['volunteer_id' => null]);
    }

    return response()->json([
        'status' => 200,
        'message' => 'Volunteer Case Numbers updated one by one successfully'
    ], 200);
}

    public function volunteer_users()
    {
        $groupHomeUsersData = DB::table('volunteer_group_homes_users')
            ->where(['is_active' => 1])
            ->select(
                'id',
                'first_name',
                'last_name'
            )
            ->orderBy('id', 'ASC')
            ->get();

        $groupHomeUsersData = DataTables::of($groupHomeUsersData)

            ->addColumn('first_name', function ($row) {
                return $row->first_name;
            })
            ->addColumn('last_name', function ($row) {
                return $row->last_name;
            })
            ->addColumn('action', function ($row) {

                return '<div class="bg-warning datatable-dropdown">
                    <a class="btn btn btn-sm  text-dark update-volunteer-group-user" button-type="edit-user" first-name=' . $row->first_name . ' last-name=' . $row->last_name . '  user-id=' . $row->id . ' href="#">Edit</a>
                    <div class="dropdown">
                        <button class="btn dropdown">
                            <i class="fa-solid fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content">
                            <a class="dropdown-item text-dark delete-volunteer-group-user" user-id=' . $row->id . ' href="#">Delete</a>
                        </div>
                    </div>
                </div>';
            })

            ->rawColumns(['first_name', 'last_name', 'action'])
            ->make(true);

        return view('admin.view_volunteer_approved_name', ['groupHomeUsersData' => $groupHomeUsersData]);
    }
    public function volunteer_getCaseNumber($id)
    {
        // $cases = DB::table('users')
        //     ->where('volunteer_id', $id)
        //     ->whereNotNull('case_number')
        //     ->pluck('case_number');

        $cases = DB::table('users')
            ->where('volunteer_id', $id)
            ->select('id', 'first_name', 'case_number')
            ->orderBy('first_name')
            ->get()
            ->toArray();

        return response()->json($cases);
    }
}
