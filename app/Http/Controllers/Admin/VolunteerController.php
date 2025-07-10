<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VersionNote;
use Illuminate\Http\Request;

class VolunteerController extends Controller
{
    public function index()
    {
    //     $notes = VersionNote::orderBy('date', 'desc')->get();
    //    return view('admin.version', compact('notes'));
       return view('admin.volunteer_approved_name_add');
    }
}



?>
