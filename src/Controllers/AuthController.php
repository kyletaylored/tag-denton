<?php
// src/Controllers/AuthController.php
namespace App\Controllers;

use App\Helpers\MongoHelper;

class AuthController
{
    public static function login($username, $password)
    {
        // Retrieve user credentials from MongoDB
        $db = MongoHelper::getMongoConnection();
        $collection = $db->selectCollection('users');
        $user = $collection->findOne(['username' => $username]);

        if ($user && password_verify($password, $user['password'])) {
            return true;
        }

        return false;
    }
}