# api-platform-book-app/symfony

## インストール

```shell
$ composer install
$ cp .env.dev .env.dev.local # その後、適切に内容を修正
```

## 使い方

```shell
# 起動
$ symfony server:start --no-tls --daemon
# その後、ブラウザで http://localhost:8000 を開く

# ログ確認
$ symfony server:log -n 100

# 終了
$ symfony server:stop
```
