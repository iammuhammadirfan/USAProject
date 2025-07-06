<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notificationMessage extends Model
{
    use HasFactory;

    protected $table = 'notification_messages';
    protected $fillable = ['title','message','status','created_at','updated_at'];
}
