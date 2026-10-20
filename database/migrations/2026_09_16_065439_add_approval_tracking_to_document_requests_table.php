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
        Schema::table('document_requests', function (Blueprint $table) {
            $table->uuid('rt_approved_by_id')->nullable()->after('verified_by_id');
            $table->uuid('rw_approved_by_id')->nullable()->after('rt_approved_by_id');
            $table->uuid('desa_approved_by_id')->nullable()->after('rw_approved_by_id');

            $table->foreign('rt_approved_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rw_approved_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('desa_approved_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropForeign(['rt_approved_by_id']);
            $table->dropForeign(['rw_approved_by_id']);
            $table->dropForeign(['desa_approved_by_id']);

            $table->dropColumn(['rt_approved_by_id', 'rw_approved_by_id', 'desa_approved_by_id']);
        });
    }
};
