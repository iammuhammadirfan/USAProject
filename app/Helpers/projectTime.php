<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class projectTime
{
    public static function get_time($id)
    {
    $user = DB::table('users')->where('id', $id)->first();
$inverty = DB::table('interval_times')->where('id', 1)->value('default_time'); // Assuming 'time' is the column name in the 'interval_times' table and it represents seconds
$distribution = DB::table('distribution_start_times')->where('id', 1)->value('default_time'); // Assuming 'start_time' is the column name in the 'distribution_start_times' table

$token = $user->token - 1;
$inttime = $inverty * $token;

// Adding the interval time to the distribution start time
$totaltime = date('g:i A', strtotime("{$distribution} + {$inttime} seconds"));

return $totaltime;


     
    }
}
