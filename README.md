<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## 📋 Giới thiệu Project
Đây là ứng dụng web được phát triển dựa trên framework **Laravel** và sử dụng **MongoDB** làm cơ sở dữ liệu.

---

## 🛠 Yêu cầu hệ thống
Trước khi bắt đầu, hãy đảm bảo máy tính của bạn đã cài đặt các công cụ sau:
* **PHP** (phiên bản tương thích với project, khuyên dùng PHP 8.2+)
* **Composer** (trình quản lý gói cho PHP)
* **MongoDB Server** và **MongoDB Compass** (để quản lý cơ sở dữ liệu)
* **Node.js & NPM** (đối với môi trường có sử dụng frontend assets)

---

## 🚀 Hướng dẫn Cài đặt & Chạy trên Máy Mới

Thực hiện lần lượt các bước sau tại terminal của bạn:

### 1. Cài đặt các thư viện PHP
Chạy lệnh sau để tải về các dependency thông qua Composer:

composer install

## 2. Thiết lập cấu hình môi trường (.env)

Sao chép file cấu hình mẫu để tạo file .env riêng cho môi trường local:

cp .env.example .env

Mở file .env vừa tạo và cập nhật lại thông số kết nối MongoDB (sử dụng chuỗi URI kết nối tới cluster cloud như mẫu dưới đây):

DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=sunburst
DB_USERNAME=chouphan1207_db_user
DB_PASSWORD=9RaaVBODoCSLqDgZ
DB_URI="mongodb+srv://chouphan1207_db_user:9RaaVBODoCSLqDgZ@sunburst.hqsiada.mongodb.net/sunburst?retryWrites=true&w=majority"

## 3. Tạo Application Key (mã hóa ứng dụng)

php artisan key:generate


## 4. Cài đặt và chạy chương trình 

npm install

npm run dev:all (chạy hết từ backend đến frontend)
