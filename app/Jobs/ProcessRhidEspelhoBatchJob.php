<?php

namespace App\Jobs;

use App\Models\RhidEspelhoBatch;
use App\Services\Rhid\RhidEspelhoBatchRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRhidEspelhoBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public function __construct(
        public int $batchId,
    ) {
        $this->timeout = max(7200, (int) config('rhid.report_timeout', 180) * 200);
    }

    public function handle(RhidEspelhoBatchRunner $runner): void
    {
        $batch = RhidEspelhoBatch::query()->find($this->batchId);
        if (! $batch) {
            return;
        }
        if (in_array($batch->status, ['completed', 'failed'], true)) {
            return;
        }

        $runner->execute($batch->fresh());
    }

    public function failed(?\Throwable $e): void
    {
        RhidEspelhoBatch::query()->where('id', $this->batchId)->where('status', '!=', 'completed')->update([
            'status' => 'failed',
            'message' => $e ? $e->getMessage() : 'Falha no processamento do lote.',
            'current_id_person' => null,
        ]);
    }
}
