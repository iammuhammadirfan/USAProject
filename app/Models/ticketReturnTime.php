<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ticketReturnTime extends Model
{
    use HasFactory;

    protected $table = 'ticket_return_times';
    protected $fillable = ['start_ticket','end_ticket','status','time'];
}
