<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('join_code', 12)->nullable()->unique()->after('owner_id');
        });
        DB::table('families')->whereNull('join_code')->orderBy('id')->each(function ($family) {
            do { $code = strtoupper(Str::random(8)); } while (DB::table('families')->where('join_code', $code)->exists());
            DB::table('families')->where('id', $family->id)->update(['join_code' => $code]);
        });
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) { $table->dropUnique(['join_code']); $table->dropColumn('join_code'); });
    }
};
