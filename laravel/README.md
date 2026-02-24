# api-platform-book-app/laravel

## インストール

```shell
$ composer install
$ cp .env.example .env # その後、適切に内容を修正
$ touch database/database.sqlite
$ php artisan migrate
```

## 使い方

```shell
# 起動
$ php artisan serve
# その後、ブラウザで http://localhost:8000 を開く
```
