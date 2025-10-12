<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');
        $client = DB::connection('mongodb')->getMongoClient();
        $database = $client->selectDatabase($db);

        try {
            $database->command([
                'dropIndexes' => 'users',
                'index' => 'email_1',
            ]);
        } catch (\Throwable $e) {
        }

        try {
            $database->command([
                'createIndexes' => 'users',
                'indexes' => [[
                    'key'   => ['email' => 1],
                    'name'  => 'email_unique_not_null',
                    'unique'=> true,
                    'partialFilterExpression' => [
                        'email' => ['$exists' => true, '$type' => 'string'],
                    ],
                ]],
            ]);
        } catch (\MongoDB\Driver\Exception\CommandException $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }

        $hasLoginIndex = false;
        $loginIndexIsUnique = false;
        $loginIndexName = null;

        $cursor = $database->command(['listIndexes' => 'users']);
        foreach ($cursor as $ix) {
            if (isset($ix->key->login) && (int)$ix->key->login === 1) {
                $hasLoginIndex = true;
                $loginIndexName = $ix->name ?? 'login_1';
                $loginIndexIsUnique = !empty($ix->unique);
                break;
            }
        }

        if ($hasLoginIndex && $loginIndexIsUnique) {
            return;
        }

        if ($hasLoginIndex && $loginIndexName) {
            try {
                $database->command([
                    'dropIndexes' => 'users',
                    'index' => $loginIndexName,
                ]);
            } catch (\Throwable $e) {
            }
        }

        $database->command([
            'createIndexes' => 'users',
            'indexes' => [[
                'key'    => ['login' => 1],
                'name'   => 'login_1',
                'unique' => true,
            ]],
        ]);
    }

    public function down(): void
    {
        $db = env('MONGODB_DATABASE', 'appdb');
        $client = DB::connection('mongodb')->getMongoClient();
        $database = $client->selectDatabase($db);

        try {
            $database->command([
                'dropIndexes' => 'users',
                'index' => 'email_unique_not_null',
            ]);
        } catch (\Throwable $e) {}

        try {
            $database->command([
                'dropIndexes' => 'users',
                'index' => 'login_1',
            ]);
        } catch (\Throwable $e) {}
    }
};
