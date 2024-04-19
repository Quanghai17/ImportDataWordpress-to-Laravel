Hướng dẫn Import Dữ liệu từ API WordPress vào Laravel
Giới thiệu
Trong dự án này, chúng ta sẽ học cách import dữ liệu từ một trang web WordPress sử dụng REST API vào một ứng dụng Laravel. Chúng ta sẽ sử dụng các công cụ Laravel như HTTP Client và Eloquent ORM để thực hiện việc này.

Yêu cầu
PHP >= 7.4
Composer
Laravel >= 8.0
Cấu hình máy chủ web cục bộ (ví dụ: XAMPP, WAMP, hoặc Laravel Valet)
Bước 1: Cài đặt Laravel
Nếu bạn chưa có một ứng dụng Laravel, bạn có thể tạo một ứng dụng mới bằng cách chạy lệnh sau trong terminal:

bash
Copy code
composer create-project --prefer-dist laravel/laravel wordpress-import
Bước 2: Xây dựng chức năng Import
2.1. Tạo Route
Mở file routes/web.php và thêm route để gọi hàm import dữ liệu từ WordPress API:

php
Copy code
use App\Http\Controllers\WordpressImportController;

Route::get('/import-from-wordpress', [WordpressImportController::class, 'importFromWordpressAPI']);
2.2. Tạo Controller
Tạo một controller mới bằng lệnh Artisan:

bash
Copy code
php artisan make:controller WordpressImportController
Sau đó, mở file WordpressImportController.php và thêm phương thức importFromWordpressAPI để thực hiện import dữ liệu từ API WordPress.

php
Copy code
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class WordpressImportController extends Controller
{
    public function importFromWordpressAPI()
    {
        // Viết logic import dữ liệu ở đây
    }
}
2.3. Thực hiện Import
Trong phương thức importFromWordpressAPI, sử dụng HTTP Client của Laravel để gọi API WordPress và xử lý dữ liệu nhận được. Bạn có thể thực hiện các thao tác như lấy bài viết, ảnh, danh mục, sau đó lưu chúng vào cơ sở dữ liệu của Laravel.

Bước 3: Cấu hình Environment
Hãy đảm bảo rằng bạn đã cấu hình các thông tin kết nối đến cơ sở dữ liệu Laravel trong file .env.

Bước 4: Chạy Ứng Dụng
Cuối cùng, bạn có thể chạy ứng dụng Laravel bằng lệnh sau:

bash
Copy code
php artisan serve
Sau đó, truy cập vào URL http://localhost:8000/import-from-wordpress để bắt đầu quá trình import từ API WordPress.