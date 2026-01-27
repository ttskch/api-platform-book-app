# Docker Hubで公開されている php イメージをベースにする
FROM php:8.5-cli

# Composerとアプリケーションの動作に最低限必要なパッケージとPHP拡張、およびSymfony CLIをインストール
RUN apt-get update \
  && apt-get install -y git unzip libpq-dev \
  && docker-php-ext-install -j$(nproc) pdo_pgsql \
  && pecl install apcu \
  && docker-php-ext-enable apcu \
  && curl -sS https://get.symfony.com/cli/installer | bash

# symfony コマンドにPATHを通す
ENV PATH="/root/.symfony5/bin:$PATH"

# コードベース内の php.ini をイメージ内の適切な場所にコピー
COPY php.ini /usr/local/etc/php/

# Docker Hubで公開されている composer イメージから composer コマンドのバイナリをコピー
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# コンテナの作業ディレクトリを /app に設定
WORKDIR /app

# Symfony CLIでローカルWebサーバーを起動
# - フォアグラウンドで起動させるために --daemon オプションなし
# - ホストOSからのアクセスを受け付けるために --allow-all-ip オプションあり
ENTRYPOINT ["symfony", "server:start", "--no-tls", "--allow-all-ip"]
