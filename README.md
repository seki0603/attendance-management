# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. `git clone git@github.com:seki0603/attendance-management.git`
2. cd attendance-management
3. mkdir docker/mysql/data
4. docker-compose up -d --build

＊MySQL は、OS によって起動しない場合があるのでそれぞれの PC に合わせて docker-compose.yml ファイルを編集してください。
<br>

### Laravel 環境構築

1. docker-compose exec php bash
2. composer install
3. .env.example ファイルから.env を作成し、環境変数を変更
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed
   <br>

### テスト実行方法

.env.testing の DB_PASSWORD に docker-compose.yml 記載の MYSQL_ROOT_PASSWORD 記述し、  
プロジェクトディレクトリ直下で実行してください。

1. docker-compose exec mysql bash
2. mysql -u root -p
3. docker-compose.yml 記載の MYSQL_ROOT_PASSWORD を入力
4. CREATE DATABASE laravel_test;
5. MySQL コンテナから抜ける
6. docker-compose exec php bash
7. php artisan key:generate --env=testing
8. vendor/bin/phpunit
   <br>

## 使用技術

- PHP 8.1.33
- Laravel 8.83.29
- MySQL 8.0.26
- nginx 1.21.1
  <br>

## ER 図

## 補足事項

### メール認証機能について

MailHog にて実装しています。
案件シートの画面遷移にて、認証誘導画面 → 認証画面と指定がありましたが、  
認証画面の Figma 参考 UI が無かったため、デザインは自作しています。  
認証誘導画面から認証画面への遷移自体は可能ですが、  
認証誘導画面時点でメールの認証を完了した場合、自動的にプロフィール設定画面に遷移します。

## URL

- 開発環境：http://localhost/
- phpMyAdmin：http://localhost:8080/
- MailHog : http://localhost:8025/
- 案件シート : 
