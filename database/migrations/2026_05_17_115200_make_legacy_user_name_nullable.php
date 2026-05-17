<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'name') && DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY name varchar(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'name') && DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET name = COALESCE(name, full_name, 'User')");
            DB::statement('ALTER TABLE users MODIFY name varchar(255) NOT NULL');
        }
    }
};
