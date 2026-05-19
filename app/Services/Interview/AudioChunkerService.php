<?php

namespace App\Services\Interview;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class AudioChunkerService
{
    /**
     * @return array{chunks: list<string>, work_dir: string}
     */
    public function prepareChunks(string $sourcePath): array
    {
        if (! is_file($sourcePath)) {
            throw new RuntimeException('Arquivo de áudio não encontrado.');
        }

        if (! $this->ffmpegAvailable()) {
            throw new RuntimeException('ffmpeg não está instalado no servidor.');
        }

        $workDir = storage_path('app/interview-tmp/'.Str::uuid());
        File::ensureDirectoryExists($workDir);

        $segmentSeconds = (int) config('interview.segment_seconds', 600);
        $normalizedPath = $this->normalizeAudio($sourcePath, $workDir);
        $chunks = $this->segmentAudio($normalizedPath, $workDir, $segmentSeconds);

        if ($chunks === []) {
            $chunks = [$normalizedPath];
        }

        return [
            'chunks' => $chunks,
            'work_dir' => $workDir,
        ];
    }

    public function cleanup(string $workDir): void
    {
        if ($workDir !== '' && is_dir($workDir)) {
            File::deleteDirectory($workDir);
        }
    }

    private function ffmpegAvailable(): bool
    {
        $process = new Process(['ffmpeg', '-version']);
        $process->run();

        return $process->isSuccessful();
    }

    private function normalizeAudio(string $sourcePath, string $workDir): string
    {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $needsReencode = in_array($extension, ['wav', 'flac', 'aiff', 'aif', 'pcm'], true);

        if (! $needsReencode && $this->fileSizeMb($sourcePath) <= 24) {
            return $sourcePath;
        }

        $output = $workDir.'/normalized.m4a';
        $process = new Process([
            'ffmpeg', '-y', '-i', $sourcePath,
            '-ac', '1', '-ar', '16000', '-b:a', '64k',
            $output,
        ]);
        $process->setTimeout(900);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($output)) {
            throw new RuntimeException('Falha ao normalizar áudio: '.Str::limit($process->getErrorOutput(), 300));
        }

        return $output;
    }

    /**
     * @return list<string>
     */
    private function segmentAudio(string $sourcePath, string $workDir, int $segmentSeconds): array
    {
        if ($this->fileSizeMb($sourcePath) <= 24) {
            return [$sourcePath];
        }

        $pattern = $workDir.'/chunk_%03d.m4a';
        $process = new Process([
            'ffmpeg', '-y', '-i', $sourcePath,
            '-f', 'segment',
            '-segment_time', (string) $segmentSeconds,
            '-c', 'copy',
            $pattern,
        ]);
        $process->setTimeout(900);
        $process->run();

        if (! $process->isSuccessful()) {
            $process = new Process([
                'ffmpeg', '-y', '-i', $sourcePath,
                '-f', 'segment',
                '-segment_time', (string) $segmentSeconds,
                '-ac', '1', '-ar', '16000', '-b:a', '64k',
                $pattern,
            ]);
            $process->setTimeout(900);
            $process->run();
        }

        $chunks = glob($workDir.'/chunk_*.m4a') ?: [];
        sort($chunks);

        return array_values($chunks);
    }

    private function fileSizeMb(string $path): float
    {
        return (filesize($path) ?: 0) / 1024 / 1024;
    }
}
