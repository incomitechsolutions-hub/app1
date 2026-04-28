<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->json('delivery_formats')->nullable()->after('delivery_format');
        });

        DB::table('courses')
            ->whereNotNull('delivery_format')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $format = trim((string) $row->delivery_format);
                    if ($format === '') {
                        continue;
                    }

                    DB::table('courses')
                        ->where('id', $row->id)
                        ->update(['delivery_formats' => json_encode([$format])]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('delivery_formats');
        });
    }
};
