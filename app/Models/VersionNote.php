namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionNote extends Model
{
    protected $table = 'version_notes';

    protected $fillable = ['date', 'version', 'details'];

    public $timestamps = true; 
}
