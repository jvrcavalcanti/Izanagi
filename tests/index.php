<?php

use App\Models\User;

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

$user = new User("Test");
var_dump($user->getFields());
