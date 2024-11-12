<?php

namespace App\Helpers;

use MongoDB\Client;
use MongoDB\Driver\ServerApi;

class MongoHelper
{
    public static function getMongoConnection()
    {
        // Use environment variables for MongoDB connection
        $mongoUri = $_ENV['MONGO_URI'] ?? null;
        $mongoDatabase = $_ENV['MONGO_DATABASE'] ?? null;

        if (!$mongoUri || !$mongoDatabase) {
            throw new \Exception('MongoDB connection details are missing from environment variables.');
        }

        // Set the version of the Stable API
        $apiVersion = new ServerApi(ServerApi::V1);

        // Create a new client with the stable API
        $client = new Client($mongoUri, [], ['serverApi' => $apiVersion]);

        try {
            // Send a ping to confirm a successful connection
            $client->selectDatabase('admin')->command(['ping' => 1]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to connect to MongoDB: ' . $e->getMessage());
        }

        // Return the database connection
        return $client->selectDatabase($mongoDatabase);
    }

    public static function getLinksCollection()
    {
        $db = self::getMongoConnection();
        $collectionName = $_ENV['MONGO_COLLECTION'] ?? 'links'; // Default to 'links' if not set
        return $db->selectCollection($collectionName);
    }
}
