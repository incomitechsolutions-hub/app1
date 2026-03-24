<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('duration_hours');
            $table->string('delivery_format', 32)->nullable()->after('price');
            $table->boolean('is_featured')->default(false)->after('delivery_format');
            $table->string('booking_url', 2048)->nullable()->after('is_featured');
            $table->string('offer_url', 2048)->nullable()->after('booking_url');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'delivery_format',
                'is_featured',
                'booking_url',
                'offer_url',
            ]);
        });
    }
};
