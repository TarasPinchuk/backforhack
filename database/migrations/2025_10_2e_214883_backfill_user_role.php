<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');
        DB::connection('mongodb')
            ->getMongoClient()
            ->selectDatabase($db)
            ->selectCollection('users')
            ->updateMany(
                ['role' => ['$exists' => false]],
                ['$set' => ['role' => 0]]
            );
    }
    public function down(): void {}
};
