<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Remove tags and atributos perigosos; mantém formatação básica de parecer/contrato.
     */
    public static function sanitizeRichText(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $allowed = '<p><br><strong><em><u><b><i><h2><h3><h4><ul><ol><li><a><div><span><table><tr><td><th><tbody><thead><tfoot>';

        $clean = strip_tags($html, $allowed);

        // Remove event handlers e javascript: em links
        $clean = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/iu', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/iu', '', $clean) ?? $clean;

        $trimmed = trim($clean);

        return $trimmed === '' ? null : $trimmed;
    }
}
