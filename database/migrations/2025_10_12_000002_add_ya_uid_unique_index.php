<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');

        DB::connection('mongodb')->getMongoClient()
          ->selectDatabase($db)
          ->command([
              'createIndexes' => 'users',
              'indexes' => [[
                  'key'   => ['ya_uid' => 1],
                  'name'  => 'ya_uid_unique',
                  'unique'=> true,
                  'partialFilterExpression' => [
                      'ya_uid' => ['$exists' => true, '$type' => 'string'],
                  ],
              ]]
          ]);
    }

    public function down(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');

        try {
            DB::connection('mongodb')->getMongoClient()
              ->selectDatabase($db)
              ->command(['dropIndexes' => 'users', 'index' => 'ya_uid_unique']);
        } catch (\Throwable $e) {}
    }
};
