<?php

return [
    'max_upload_mb' => (int) env('INTERVIEW_MAX_UPLOAD_MB', 500),
    'keep_audio' => (bool) env('INTERVIEW_KEEP_AUDIO', true),
    'whisper_model' => env('INTERVIEW_WHISPER_MODEL', 'whisper-1'),
    'segment_seconds' => (int) env('INTERVIEW_SEGMENT_SECONDS', 600),
    'whisper_timeout' => (int) env('INTERVIEW_WHISPER_TIMEOUT', 300),
    'extraction_timeout' => (int) env('INTERVIEW_EXTRACTION_TIMEOUT', 180),
];
