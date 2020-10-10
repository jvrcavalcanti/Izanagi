<?php

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

autoload("./src");
autoload("./tests");

define("DB_CONFIG", [
    'name' => "pendragon",
    'user' => "accolon",
    'password' => 'password',
    'driver' => "sqlite"
]);

$manager = new Manager([
    User::class
]);

// $manager->migrate();

$user = new User();
$user->name = 'kk';
$user->password = '123';
$user->admin = true;
dd($user->save());
