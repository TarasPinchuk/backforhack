<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');
        $client = DB::connection('mongodb')->getMongoClient();

        $client->selectDatabase($db)->command([
            'createIndexes' => 'users',
            'indexes' => [[
                'key'   => ['login' => 1],
                'name'  => 'login_unique',
                'unique'=> true,
            ]],
        ]);

        $client->selectDatabase($db)->command([
            'createIndexes' => 'users',
            'indexes' => [[
                'key'  => ['created_at' => -1],
                'name' => 'users_created_at_desc',
            ]],
        ]);
    }

    public function down(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');
        $client = DB::connection('mongodb')->getMongoClient();
        foreach (['login_unique','users_created_at_desc'] as $name) {
            try {
                $client->selectDatabase($db)->command(['dropIndexes' => 'users', 'index' => $name]);
            } catch (\Throwable $e) {}
        }
    }
};
