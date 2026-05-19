<?php

namespace App\Services\Interview;

use App\Models\AiSetting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiWhisperService
{
    public function transcribeFile(string $filePath, AiSetting $setting): string
    {
        $key = $setting->safeTranscriptionApiKey();
        if ($key === null || $key === '') {
            throw new RuntimeException(
                $setting->provider === 'openai'
                    ? 'Chave OpenAI não configurada para transcrição.'
                    : 'Configure a chave OpenAI para transcrição (Whisper) nas configurações de IA.'
            );
        }

        $timeout = (int) config('interview.whisper_timeout', 300);
        $model = (string) config('interview.whisper_model', 'whisper-1');

        try {
            $response = Http::timeout($timeout)
                ->withToken($key)
                ->attach('file', fopen($filePath, 'r'), basename($filePath))
                ->post('https://api.openai.com/v1/audio/transcriptions', [
                    'model' => $model,
                    'language' => 'pt',
                    'response_format' => 'json',
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Timeout ou falha de conexão com OpenAI Whisper: '.$e->getMessage(), 0, $e);
        }

        if (! $response->successful()) {
            Log::warning('Whisper API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('Falha na API Whisper: HTTP '.$response->status());
        }

        $data = $response->json();
        $text = $data['text'] ?? '';

        return is_string($text) ? trim($text) : '';
    }

    /**
     * @param  list<string>  $chunkPaths
     */
    public function transcribeChunks(array $chunkPaths, AiSetting $setting): string
    {
        $parts = [];
        foreach ($chunkPaths as $index => $chunkPath) {
            $text = $this->transcribeFile($chunkPath, $setting);
            if ($text !== '') {
                $parts[] = $text;
            }
            Log::info('Whisper chunk transcribed', ['chunk' => $index + 1, 'chars' => strlen($text)]);
        }

        return trim(implode("\n\n", $parts));
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(AiSetting $setting): array
    {
        try {
            $key = $setting->safeTranscriptionApiKey();
            if ($key === null || $key === '') {
                return ['ok' => false, 'message' => 'Informe a chave OpenAI para transcrição.'];
            }

            $response = Http::timeout(30)
                ->withToken($key)
                ->get('https://api.openai.com/v1/models');

            if (! $response->successful()) {
                return ['ok' => false, 'message' => 'HTTP '.$response->status().': '.$response->body()];
            }

            return ['ok' => true, 'message' => 'Conexão com OpenAI (Whisper) OK.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
