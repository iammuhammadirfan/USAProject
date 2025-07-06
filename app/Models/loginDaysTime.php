<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class loginDaysTime extends Model
{
    use HasFactory;

    protected $table = 'login_days_time';
    protected $fillable = ['day','start_time','end_time','status','date','is_active'];
}
