# 「API Platformを活用したPHPによる本格的なWeb API開発」<wbr>サンプルコード配布サイト

[<img src="cover.jpg" height="400" alt="「API Platformを活用したPHPによる本格的なWeb API開発」のカバー画像">](https://gihyo.jp/book/2026/978-4-297-15491-2)

この度は[「API Platformを活用したPHPによる本格的なWeb API開発」](https://gihyo.jp/book/2026/978-4-297-15491-2) にご興味をお持ちいただきありがとうございます。

本リポジトリでは、書籍内で作成したサンプルアプリケーションの実装コードを配布しております。

## サンプルコードの構成

サンプルコードは以下の3つのディレクトリによって構成されています。

| ディレクトリ | 内容 |
| --- | --- |
| [`symfony/`](symfony) | SymfonyベースのAPI Platformアプリケーション |
| [`laravel/`](laravel) | LaravelベースのAPI Platformアプリケーション |
| [`frontend/`](frontend) | フロントエンドアプリケーション |

書籍内では、`api-platform-book-app/` ディレクトリをプロジェクトルートディレクトリとして、以下のようにコードベースを構築しました。

- 第1章から第10章までは、プロジェクトルートディレクトリ直下にSymfonyベースのAPI Platformアプリケーションを構築
- 第11章で、API Platformアプリケーションのコードベースを丸ごと `api-platform-book-app/backend/` ディレクトリ配下に移動し、`api-platform-book-app/frontend/` ディレクトリ配下にフロントエンドアプリケーションを構築

本リポジトリでは、書籍内で詳しく解説したSymfonyベースでの実装に加え、書籍内では補足説明をするに留めたLaravelベースでの実装についても完全に動作するものを配布しています。

そのため、書籍内におけるディレクトリ構成とは若干異なり、プロジェクトルートディレクトリ直下に初めから `symfony/` ディレクトリと `laravel/` ディレクトリを配置する構成としてあります。書籍を読みながら参照される際は、この点に何卒ご留意ください。

なお、サンプルコードは書籍の解説に沿って [適切な粒度でコミットが分けられています](https://github.com/ttskch/api-platform-book-app/commits/main/) ので、お手元でサンプルアプリケーションを構築していく中で、上手く動作しないときなどにその時点の正しいコードの参考例としても活用いただけます。

## サンプルコードのダウンロード方法

以下のような手順で本リポジトリを `git clone` してご利用ください。

```shell
git clone git@github.com:ttskch/api-platform-book-app.git
```

あるいは、以下のURLからZIP形式でダウンロードいただくことも可能です。

<https://github.com/ttskch/api-platform-book-app/archive/refs/heads/main.zip>

## 更新履歴と取り込み方法

本リポジトリでは、読者にとってより有益な内容となるよう、書籍の内容と乖離しない範囲でコードやコミットログを更新する場合があります。これまでの更新履歴は以下のとおりです。

| 日付 | 更新内容 |
| --- | --- |
| 2026/02/20 | 初版公開 |
| 2026/02/25 | Laravel版のコードベースの微修正と、READMEの修正 | 

コミットログごと上書き更新する関係上、`git clone` によってダウンロードいただいたローカルリポジトリを新しいバージョンに更新する場合には以下のような手順による強制更新が必要となります。

```shell
git fetch origin && git reset --hard origin/main
```

もしローカルリポジトリに読者によるコミットが追加されている場合には、`reset` ではなく `rebase` を用いて以下のように更新することで追加のコミットを維持したままベースを最新化できます。

```shell
git fetch origin && git rebase --onto origin/main origin/main@{1} main
```

## お問い合わせ

書籍やサンプルコードの内容についてのお問い合わせは [こちらのページ](https://gihyo.jp/book/2026/978-4-297-15491-2) の `お問い合わせ` リンクよりお願いいたします。
