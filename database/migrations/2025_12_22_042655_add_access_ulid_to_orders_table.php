<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom access_ulid untuk secure order access
     * tanpa mengexpose admin panel URL structure
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('access_ulid', 26)->nullable()->unique()->after('order_number');
        });

        // Backfill existing orders dengan ULID
        \App\Models\Order::whereNull('access_ulid')->each(function ($order) {
            $order->update(['access_ulid' => (string) \Illuminate\Support\Str::ulid()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['access_ulid']);
            $table->dropColumn('access_ulid');
        });
    }
};
