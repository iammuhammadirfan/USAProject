<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\generatedTicket;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // protected $appends = ['projectTime'];

    protected $table = 'users';
    protected $fillable = ['is_picked_warning','is_warning','idcard_issued_date','id','first_name','last_name','case_number','status','proxy','is_active','token','last_token_date','file','is_enabled','date_of_birth','id_card'];

    // public function getProjectTimeAttribute()
    // {
    //     $distribution = \DB::table('distribution_start_times')->where('id', 1)->first();
    //     $interval = \DB::table('interval_times')->where('id', 1)->first();
    //     $user = User::find($this->id); // Fetching the user by ID
        
    //     $defaultTime = strtotime($distribution->default_time);
        
    //     // Calculate the project time based on the user's token number
        
    // $projectTime = date('h:i A', $defaultTime + ($interval->default_time * ($user->token ?? 0)));

        
    //     return $projectTime;
    // }

    public function user_tickets()
    {
        return $this->hasMany(generatedTicket::class, 'user_id');
    }

    public function hasAnyRoles($roles)
    {
        return $this->roles()->where('name',$roles)
        ->first() ? true:false;
    }


  
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
  

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    
}
