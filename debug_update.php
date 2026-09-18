require __DIR__ . '/vendor/autoload.php';  ;
 = require __DIR__ . '/bootstrap/app.php';  ;
 = - ;
- ;
use App\\Models\\User;  ;
use App\\Http\\Controllers\\Admin\\VoterController;  ;
use Illuminate\\Http\\Request;  ;
 = User::factory()-, 'identity_number' =, 'password' =, 'login_password' =, 'is_active' = ;
 = User::factory()-, 'identity_number' =, 'password' = ;
 = Request::create('/admin/voters/' . - . '/password', 'PUT', ['password' =, 'password_confirmation' =; 
app('auth')- ;
 = new VoterController();  ;
 = -, - ;
 = - ;
var_dump(- ;
var_dump(Illuminate\\Support\\Facades\\Hash::check('NewSecurePass123', - 
