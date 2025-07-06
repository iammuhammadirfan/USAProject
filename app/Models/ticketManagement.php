<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ticketManagement extends Model
{
    use HasFactory;

    protected $table = 'tickets_management';
    protected $fillable = ['ticket_number','ticket_limit','ticket_limit_status','created_at','updated_at'];
}
