<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_cards', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('list_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'is_archived']);
        });

        // Migração de dados: cartões herdados de quadros por empresa recebem company_id.
        $rows = DB::table('task_cards')
            ->join('task_lists', 'task_lists.id', '=', 'task_cards.list_id')
            ->join('task_boards', 'task_boards.id', '=', 'task_lists.board_id')
            ->whereNotNull('task_boards.company_id')
            ->select('task_cards.id as card_id', 'task_boards.company_id as company_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('task_cards')
                ->where('id', $row->card_id)
                ->update(['company_id' => $row->company_id]);
        }
    }

    public function down(): void
    {
        Schema::table('task_cards', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'is_archived']);
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};

