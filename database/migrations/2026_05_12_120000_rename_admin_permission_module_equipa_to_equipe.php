<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_user_permissions')
            ->where('module', 'equipa')
            ->update(['module' => 'equipe']);
    }

    public function down(): void
    {
        DB::table('admin_user_permissions')
            ->where('module', 'equipe')
            ->update(['module' => 'equipa']);
    }
};
