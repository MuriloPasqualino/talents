<?php

namespace App\Services\Interview;

use App\Models\Interview;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

class InterviewReportRenderer
{
    public function loadInterview(int $interviewId): Interview
    {
        return Interview::query()
            ->with([
                'questionnaire.sections.questions',
                'answers.question.section',
                'company:id,name',
                'createdBy:id,name',
            ])
            ->findOrFail($interviewId);
    }

    public function pdf(Interview $interview): \Barryvdh\DomPDF\PDF
    {
        $this->ensureDompdfWritableDirs();

        $fontDir = storage_path('fonts');
        $tempDir = storage_path('app/dompdf-tmp');
        $chroot = realpath(base_path()) ?: base_path();

        $pdf = Pdf::loadView('admin.entrevistas.report-pdf', [
            'interview' => $interview,
            'sections' => $this->groupedSections($interview),
        ]);

        $pdf->setOption('fontDir', $fontDir);
        $pdf->setOption('fontCache', $fontDir);
        $pdf->setOption('tempDir', $tempDir);
        $pdf->setOption('chroot', $chroot);

        return $pdf->setPaper('a4');
    }

    public function docx(Interview $interview): string
    {
        $phpWord = new PhpWord;
        $section = $phpWord->addSection();

        $section->addTitle('Relatório de Entrevista', 1);
        $section->addText('Candidato: '.$interview->candidate_name);
        if ($interview->position_title) {
            $section->addText('Vaga: '.$interview->position_title);
        }
        $section->addText('Roteiro: '.$interview->questionnaire->name);
        $section->addText('Data: '.($interview->finished_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i') ?? '—'));
        $section->addTextBreak(1);

        foreach ($this->groupedSections($interview) as $block) {
            $section->addTitle($block['title'], 2);
            foreach ($block['items'] as $item) {
                $section->addText($item['question'], ['bold' => true]);
                $section->addText($item['answer'], [], ['alignment' => Jc::BOTH]);
                $section->addTextBreak(1);
            }
        }

        if ($interview->transcript_text) {
            $section->addPageBreak();
            $section->addTitle('Transcrição completa', 2);
            $section->addText($interview->transcript_text);
        }

        $path = storage_path('app/interview-tmp/report-'.$interview->id.'-'.uniqid().'.docx');
        File::ensureDirectoryExists(dirname($path));
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }

    /**
     * @return list<array{title: string, items: list<array{question: string, answer: string}>}>
     */
    public function groupedSections(Interview $interview): array
    {
        $answersByQuestionId = $interview->answers->keyBy('question_id');
        $out = [];

        foreach ($interview->questionnaire->sections as $section) {
            $items = [];
            foreach ($section->questions as $question) {
                $answer = $answersByQuestionId->get($question->id);
                $items[] = [
                    'question' => $question->text,
                    'answer' => $answer?->answer ?? 'Não mencionado',
                ];
            }
            $out[] = [
                'title' => $section->title,
                'items' => $items,
            ];
        }

        return $out;
    }

    private function ensureDompdfWritableDirs(): void
    {
        foreach ([storage_path('fonts'), storage_path('app/dompdf-tmp')] as $dir) {
            File::ensureDirectoryExists($dir);
        }
    }
}
