<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'reports_enabled' => (bool) env('OPENAI_REPORTS_ENABLED', false),
    'report_model' => env('OPENAI_REPORT_MODEL', 'gpt-4.1-mini'),
    'report_max_output_tokens' => (int) env('OPENAI_REPORT_MAX_OUTPUT_TOKENS', 180),
    'report_include_rows' => (bool) env('OPENAI_REPORT_INCLUDE_ROWS', false),
    'report_top_rows' => (int) env('OPENAI_REPORT_TOP_ROWS', 5),
];
