<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersionNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VolunteerController extends Controller
{
    public function index()
    {
        //     $notes = VersionNote::orderBy('date', 'desc')->get();
        //    return view('admin.version', compact('notes'));
        return view('admin.volunteer_approved_name_add');
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
}
