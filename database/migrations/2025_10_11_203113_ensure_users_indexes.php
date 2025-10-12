<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;          
use MongoDB\Laravel\Schema\Blueprint;           

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::table('users', function (Blueprint $collection) {
            $collection->index('login', options: ['unique' => true]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $collection) {
            $collection->dropIndex('login_1');
        });
    }
};
