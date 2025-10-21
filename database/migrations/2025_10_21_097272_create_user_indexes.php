<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');

        try {
            DB::connection('mongodb')->getMongoClient()
              ->selectDatabase($db)
              ->command(['dropIndexes' => 'users', 'index' => 'login_1']);
        } catch (\Throwable $e) {}

        DB::connection('mongodb')->getMongoClient()
          ->selectDatabase($db)
          ->command([
              'createIndexes' => 'users',
              'indexes' => [[
                  'key' => ['login' => 1],
                  'name' => 'login_unique',
                  'unique' => true,
              ]],
          ]);
    }

    public function down(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');
        try {
            DB::connection('mongodb')->getMongoClient()
              ->selectDatabase($db)
              ->command(['dropIndexes' => 'users', 'index' => 'login_unique']);
        } catch (\Throwable $e) {}
    }
};
