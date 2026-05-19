<?php

namespace App\Jobs;

use App\Enums\InterviewStatus;
use App\Models\AiSetting;
use App\Models\Interview;
use App\Models\InterviewAnswer;
use App\Models\InterviewQuestionnaireQuestion;
use App\Services\Interview\AudioChunkerService;
use App\Services\Interview\InterviewAnswerExtractor;
use App\Services\Interview\OpenAiWhisperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessInterviewAudioJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct(
        public int $interviewId
    ) {}

    public function handle(
        AudioChunkerService $chunker,
        OpenAiWhisperService $whisper,
        InterviewAnswerExtractor $extractor
    ): void {
        $interview = Interview::query()->find($this->interviewId);
        if (! $interview) {
            return;
        }

        $setting = AiSetting::current();
        if (! $setting || $setting->safeTranscriptionApiKey() === null) {
            $interview->markFailed('Configure a chave OpenAI para transcrição nas configurações de IA.');

            return;
        }

        if (! $setting->is_enabled || $setting->safeApiKey() === null) {
            $interview->markFailed('IA desabilitada ou sem chave para extração do relatório.');

            return;
        }

        $audioPath = $interview->audioAbsolutePath();
        if (! $audioPath || ! is_file($audioPath)) {
            $interview->markFailed('Arquivo de áudio não encontrado no storage.');

            return;
        }

        $workDir = '';

        try {
            $interview->markProcessing(InterviewStatus::Transcribing);

            $prepared = $chunker->prepareChunks($audioPath);
            $workDir = $prepared['work_dir'];
            $transcript = $whisper->transcribeChunks($prepared['chunks'], $setting);

            if (trim($transcript) === '') {
                throw new \RuntimeException('Transcrição vazia. Verifique a qualidade do áudio.');
            }

            $interview->update(['transcript_text' => $transcript]);
            $interview->markProcessing(InterviewStatus::Extracting);

            $questions = InterviewQuestionnaireQuestion::query()
                ->whereHas('section', fn ($q) => $q->where('questionnaire_id', $interview->questionnaire_id))
                ->with('section')
                ->orderBy('section_id')
                ->orderBy('position')
                ->get();

            $extracted = $extractor->extract($transcript, $questions, $setting);
            $questionsByKey = $questions->keyBy('question_key');

            DB::transaction(function () use ($interview, $extracted, $questionsByKey) {
                InterviewAnswer::query()->where('interview_id', $interview->id)->delete();

                foreach ($extracted as $row) {
                    $question = $questionsByKey->get($row['question_key']);
                    if (! $question) {
                        continue;
                    }

                    InterviewAnswer::query()->create([
                        'interview_id' => $interview->id,
                        'question_id' => $question->id,
                        'answer' => $row['answer'],
                        'raw_quote' => $row['raw_quote'],
                    ]);
                }
            });

            $interview->markCompleted();

            if (! config('interview.keep_audio', true) && $interview->audio_path) {
                Storage::disk('local')->delete($interview->audio_path);
                $interview->update(['audio_path' => null]);
            }
        } catch (\Throwable $e) {
            Log::error('ProcessInterviewAudioJob failed', [
                'interview_id' => $this->interviewId,
                'message' => $e->getMessage(),
            ]);
            $interview->markFailed($e->getMessage());
            throw $e;
        } finally {
            if ($workDir !== '') {
                $chunker->cleanup($workDir);
            }
        }
    }
}
