<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\generatedTicket;
use App\Helper\Helper;

class ticketBeingServed
{
    public static function get_ticket_served()
    {
        $recently_updated_record = generatedTicket::where('is_cancelled',0)
        // whereDate('created_at', Carbon::today())
            // ->where('is_cancelled',0)
            ->where('is_served',1)
            ->where('status',1)
            ->orderBy('updated_at','DESC')
            ->select('ticket_number')
            ->first();

            $reset_recent_records = generatedTicket::where('is_cancelled',0)
            // whereDate('created_at', Carbon::today())
            // ->where('is_cancelled',0)
            ->where('is_served',1)
            ->where('status',1)
            ->pluck('is_served')
            ->toArray();

            if($recently_updated_record)
            {
                $sorted_values=array_count_values($reset_recent_records);

                if($sorted_values['1'] == count($reset_recent_records))
                {
                    return response()->json([
                        'ticket_number'=>$recently_updated_record->ticket_number
                    ]);
                }

                return response()->json([
                    'ticket_number'=>$recently_updated_record->ticket_number
                ]);
            } else {
                return response()->json([
                    'ticket_number'=>0
                ]);
            }
    }
}
