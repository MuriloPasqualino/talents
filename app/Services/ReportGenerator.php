<?php

namespace App\Services;

use App\Models\Survey;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportGenerator
{
    public function executivePdf(Survey $survey): \Barryvdh\DomPDF\PDF
    {
        $survey->load([
            'company',
            'template.sections',
            'results' => fn ($q) => $q->orderBy('id'),
            'insights',
        ]);

        return Pdf::loadView('reports.executive', [
            'survey' => $survey,
        ])->setPaper('a4');
    }

    public function technicalPdf(Survey $survey): \Barryvdh\DomPDF\PDF
    {
        $survey->load([
            'company',
            'template.sections.questions',
            'results' => fn ($q) => $q->orderBy('id'),
            'insights',
            'responses',
        ]);

        return Pdf::loadView('reports.technical', [
            'survey' => $survey,
        ])->setPaper('a4');
    }
}
