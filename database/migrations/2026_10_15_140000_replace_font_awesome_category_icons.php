<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Font Awesome non è più caricato: le due categorie che lo usavano passano a Bootstrap Icons.
     */
    private const ICONS = [
        'fa fa-shopping-bag' => 'bi bi-bag',
        'fa fa-leaf' => 'bi bi-house-heart',
    ];

    public function up(): void
    {
        foreach (self::ICONS as $old => $new) {
            DB::table('categories')->where('icon', $old)->update(['icon' => $new]);
        }
    }

    public function down(): void
    {
        foreach (self::ICONS as $old => $new) {
            DB::table('categories')->where('icon', $new)->update(['icon' => $old]);
        }
    }
};
