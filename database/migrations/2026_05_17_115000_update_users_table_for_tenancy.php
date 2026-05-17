<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'tenant_id')) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Default Tenant',
                'code' => 'DEFAULT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });

            DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
        }

        if (! Schema::hasColumn('users', 'branch_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id')->index();
            });
        }

        if (! Schema::hasColumn('users', 'full_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('full_name')->nullable()->after('branch_id');
            });

            if (Schema::hasColumn('users', 'name')) {
                DB::table('users')->whereNull('full_name')->update(['full_name' => DB::raw('name')]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }

            if (Schema::hasColumn('users', 'branch_id')) {
                $table->dropColumn('branch_id');
            }

            if (Schema::hasColumn('users', 'full_name')) {
                $table->dropColumn('full_name');
            }
        });
    }
};
