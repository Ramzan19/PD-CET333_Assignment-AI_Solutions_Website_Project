<?php
define('DB_HOST', getenv('AI_SOLUTIONS_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('AI_SOLUTIONS_DB_NAME') ?: 'ai_solutions_db');
define('DB_USER', getenv('AI_SOLUTIONS_DB_USER') ?: 'root');
define('DB_PASS', getenv('AI_SOLUTIONS_DB_PASS') ?: '');
define('SITE_NAME', 'AI-Solutions');
// Google Analytics 4 Measurement ID (e.g. 'G-XXXXXXXXXX'). Empty = analytics disabled.
// Set the AI_SOLUTIONS_GA_ID env var (or edit here) to activate site tracking.
define('GA_MEASUREMENT_ID', getenv('AI_SOLUTIONS_GA_ID') ?: '');
// --- Claude (Anthropic) assistant ----------------------------------------
// Primary, most-intelligent chatbot brain. Set the ANTHROPIC_API_KEY env var
// (or paste your key below) to switch the assistant onto the Claude API.
// Get a key at https://console.anthropic.com/. When no key is set the chatbot
// gracefully falls back to the keyless LLM below, then to built-in answers.
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');
// Default to Anthropic's most capable model. Override with the ANTHROPIC_MODEL
// env var if you prefer a faster/cheaper option (e.g. claude-haiku-4-5).
define('ANTHROPIC_MODEL', getenv('ANTHROPIC_MODEL') ?: 'claude-opus-4-8');
define('ANTHROPIC_VERSION', '2023-06-01');

// Keyless OpenAI-compatible endpoint (Pollinations) so the chatbot can use a live LLM out of the box.
// To use your own provider (e.g. OpenAI), set AI_ASSISTANT_API_URL + AI_ASSISTANT_API_KEY env vars.
define('AI_ASSISTANT_API_URL', getenv('AI_ASSISTANT_API_URL') ?: 'https://text.pollinations.ai/openai');
define('AI_ASSISTANT_API_KEY', getenv('AI_ASSISTANT_API_KEY') ?: '');
define('AI_ASSISTANT_MODEL', getenv('AI_ASSISTANT_MODEL') ?: 'openai-fast');
define('AI_ASSISTANT_TIMEOUT', (int) (getenv('AI_ASSISTANT_TIMEOUT') ?: 8));
