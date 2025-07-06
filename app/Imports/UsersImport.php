<?php
  
namespace App\Imports;
  
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class UsersImport implements ToModel , WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
{

    $existingUser = User::where([
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'date_of_birth' => $row['date_of_birth'],
      
    ])->exists();

  
    if (!$existingUser) {
       return new User([
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'date_of_birth' => $row['date_of_birth'],
        'case_number' => $row['case_number'],
        'role' => $row['role'],
        // You may add other fields as needed
    ]);
    }

    

    // If date_of_birth is not in the specific format, create User model with the original value
   
}

}