<?php

namespace App\Controllers;

use App\Helpers\MongoHelper;

class AuthController
{
    public static function login($username, $password)
    {
        session_start();

        // Retrieve user credentials from MongoDB
        $db = MongoHelper::getMongoConnection();
        $collection = $db->selectCollection('users');
        $user = $collection->findOne(['username' => $username]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            return true;
        }

        return false;
    }

    public static function logout()
    {
        session_start();
        session_destroy();
    }

    public static function isAuthenticated()
    {
        session_start();
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'];
    }
}
