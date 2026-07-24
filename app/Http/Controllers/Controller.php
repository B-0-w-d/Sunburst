<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// Lớp Controller cơ sở trừu tượng (abstract base controller) cho toàn bộ ứng dụng
abstract class Controller extends BaseController
{
    // Kích hoạt các tính năng cốt lõi hỗ trợ phân quyền, điều phối job và kiểm tra tính hợp lệ dữ liệu
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
