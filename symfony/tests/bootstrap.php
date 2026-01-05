<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// ログディレクトリを自動で作成
if (!is_dir($logDir = dirname(__DIR__).'/var/log')) {
    mkdir($logDir, 0777, true);
}
