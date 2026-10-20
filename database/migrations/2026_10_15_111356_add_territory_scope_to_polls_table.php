<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->string('level')->default('DESA')->after('status'); // DESA, RW, RT
            $table->foreignUuid('village_id')->nullable()->after('level')->constrained('villages')->nullOnDelete();
            $table->foreignUuid('rw_id')->nullable()->after('village_id')->constrained('rws')->nullOnDelete();
            $table->foreignUuid('rt_id')->nullable()->after('rw_id')->constrained('rts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign(['rt_id']);
            $table->dropForeign(['rw_id']);
            $table->dropForeign(['village_id']);
            $table->dropColumn(['level', 'village_id', 'rw_id', 'rt_id']);
        });
    }
};
