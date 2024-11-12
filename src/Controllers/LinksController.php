<?php

namespace App\Controllers;

use App\Helpers\MongoHelper;

class LinksController
{
    public static function getAllLinks()
    {
        $db = MongoHelper::getMongoConnection();
        $collection = $db->selectCollection('links');

        // Fetch all links from the collection
        $cursor = $collection->find();
        $links = [];
        foreach ($cursor as $document) {
            $links[] = [
                'url' => $document['url'],
                'key' => $document['key'],
                'redirect' => $document['data']['ios_scheme'] ?? $document['data']['android_scheme'] ?? $document['url']
            ];
        }

        return $links;
    }
}
