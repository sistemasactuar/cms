<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasColumn(string $column): bool
    {
        return Schema::hasColumn('applications', $column);
    }

    private function anchorFor(array $candidates, string $fallback = 'name'): string
    {
        foreach ($candidates as $candidate) {
            if ($this->hasColumn($candidate)) {
                return $candidate;
            }
        }

        return $fallback;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!$this->hasColumn('url')) {
            $anchor = $this->anchorFor(['version', 'description', 'name']);

            Schema::table('applications', function (Blueprint $table) use ($anchor): void {
                $table->string('url')->nullable()->after($anchor);
            });
        }

        if (!$this->hasColumn('publico')) {
            $anchor = $this->anchorFor(['url', 'status', 'version', 'name']);

            Schema::table('applications', function (Blueprint $table) use ($anchor): void {
                $table->boolean('publico')->default(true)->after($anchor);
            });
        }

        if (!$this->hasColumn('manuals')) {
            $anchor = $this->anchorFor(['publico', 'url', 'status', 'version', 'name']);

            Schema::table('applications', function (Blueprint $table) use ($anchor): void {
                $table->json('manuals')->nullable()->after($anchor);
            });
        }

        if (!$this->hasColumn('videos')) {
            $anchor = $this->anchorFor(['manuals', 'publico', 'url', 'status', 'version', 'name']);

            Schema::table('applications', function (Blueprint $table) use ($anchor): void {
                $table->json('videos')->nullable()->after($anchor);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [];

        foreach (['videos', 'manuals', 'publico', 'url'] as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        if ($columns === []) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
