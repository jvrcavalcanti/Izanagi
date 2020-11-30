<?php

require "./vendor/autoload.php";

use Accolon\Izanagi\Manager;
use Accolon\Izanagi\QueryBuilder;
use App\Models\User;

function dd($var)
{
    var_dump($var);
    die();
}

function autoload($dir = "./")
{
    foreach (scandir($dir) ?? [] as $file) {
        if ($file == "." || $file == "..") {
            continue;
        }

        $fullname = $dir . "/$file";
        
        if (is_dir($fullname)) {
            autoload($fullname);
            continue;
        }

        if (is_file($fullname)) {
            require_once $fullname;
            continue;
        }
    }
}

autoload("./tests");

define("DB_CONFIG", [
    'name' => "izanagi",
    'user' => "accolon",
    'password' => 'password',
    'driver' => "mysql"
]);

$manager = new Manager([
    User::class
]);

// $manager->migrate();

$user = new User;
dd($user->findAll());
