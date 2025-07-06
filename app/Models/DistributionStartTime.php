<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionStartTime extends Model
{
    use HasFactory;

    protected $table = 'distribution_start_times';
    protected $fillable = ['default_time'];
}
