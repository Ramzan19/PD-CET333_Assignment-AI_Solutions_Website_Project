<?php

function assistant_clean_text($value, $max_length = 1000) {
    $value = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max_length);
    }
    return substr($value, 0, $max_length);
}

function assistant_lower($value) {
    $value = (string) $value;
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}

function assistant_action($label, $data) {
    return array_merge(['label' => $label], $data);
}

function assistant_default_actions() {
    return [
        assistant_action('Find my solution', ['prompt' => 'Which AI solution fits my business?']),
        assistant_action('Schedule demo', ['route' => 'schedule-demo.php']),
        assistant_action('Pricing help', ['prompt' => 'How much does an AI solution cost?']),
        assistant_action('Human handover', ['handover' => 'Sales Representative']),
    ];
}

function assistant_intents() {
    return [
        'services' => [
            'topic' => 'Software Assistance',
            'keywords' => ['service', 'services', 'software', 'solution', 'solutions', 'build', 'recommend', 'fit', 'offer', 'help my business'],
            'reply' => 'AI-Solutions can help with four practical paths: AI assistants for customer conversations, workflow automation for repeat tasks, analytics dashboards for operational decisions, and rapid prototypes for testing ideas before a full build. Tell me your industry and the workflow you want to improve, and I can narrow the best starting point.',
            'actions' => [
                ['label' => 'View services', 'route' => 'services.php'],
                ['label' => 'Compare options', 'prompt' => 'Compare assistant, automation, dashboard, and prototype options'],
                ['label' => 'Book a walkthrough', 'route' => 'schedule-demo.php'],
            ],
        ],
        'demo' => [
            'topic' => 'Schedule Demo',
            'keywords' => ['demo', 'schedule', 'book', 'walkthrough', 'meeting', 'consultation', 'call', 'appointment'],
            'reply' => 'A demo is the quickest way to turn a broad idea into a practical plan. Share what you want to improve, your preferred date, and the type of solution you are exploring, and the team can prepare a focused walkthrough.',
            'actions' => [
                ['label' => 'Open demo form', 'route' => 'schedule-demo.php'],
                ['label' => 'What to prepare', 'prompt' => 'What should I prepare before an AI demo?'],
                ['label' => 'Human handover', 'handover' => 'Schedule Demo'],
            ],
        ],
        'automation' => [
            'topic' => 'Workflow Automation',
            'keywords' => ['automation', 'automate', 'workflow', 'manual', 'approval', 'notification', 'operations', 'process', 'handoff', 'routing', 'follow up', 'repeat'],
            'reply' => 'For automation, the best starting point is usually one repeatable workflow with clear triggers and outcomes. Examples include lead routing, demo reminders, approval steps, customer intake, status updates, and reporting. If you share the manual task, I can map a likely automation flow.',
            'actions' => [
                ['label' => 'Map a workflow', 'prompt' => 'Help me map a workflow automation idea'],
                ['label' => 'View services', 'route' => 'services.php'],
                ['label' => 'Talk to team', 'handover' => 'Workflow Automation'],
            ],
        ],
        'assistant' => [
            'topic' => 'Virtual Assistant',
            'keywords' => ['assistant', 'chatbot', 'support', 'customer', 'visitor', 'agent', 'bot', 'faq', 'lead capture', 'conversation'],
            'reply' => 'A realistic AI assistant should answer common questions, qualify intent, collect useful context, and move complex cases to a human with a clean summary. AI-Solutions can be shaped for sales, support, events, education, retail, or internal operations.',
            'actions' => [
                ['label' => 'Try assistant page', 'route' => 'chatbot.php'],
                ['label' => 'Assistant use cases', 'prompt' => 'Give me AI assistant use cases for my business'],
                ['label' => 'Request handover', 'handover' => 'Virtual Assistant'],
            ],
        ],
        'analytics' => [
            'topic' => 'Data Analytics',
            'keywords' => ['analytics', 'dashboard', 'report', 'data', 'insight', 'metrics', 'kpi', 'chart', 'tracking', 'performance'],
            'reply' => 'A dashboard can turn inquiries, demo bookings, chatbot handovers, event registrations, visitor activity, and feedback into decision-ready signals. Good dashboards usually start with three questions: what changed, why it changed, and what action should happen next.',
            'actions' => [
                ['label' => 'Dashboard examples', 'prompt' => 'What should an AI dashboard track?'],
                ['label' => 'View services', 'route' => 'services.php'],
                ['label' => 'Schedule demo', 'route' => 'schedule-demo.php'],
            ],
        ],
        'prototype' => [
            'topic' => 'Prototype',
            'keywords' => ['prototype', 'mvp', 'proof of concept', 'poc', 'mockup', 'test idea', 'pilot', 'demo product'],
            'reply' => 'A prototype is useful when you want to test an idea quickly before investing in a full system. AI-Solutions can turn a workflow, dashboard, assistant, or automation concept into a working demo that stakeholders can review and improve.',
            'actions' => [
                ['label' => 'Prototype plan', 'prompt' => 'Help me plan an AI prototype'],
                ['label' => 'Book demo', 'route' => 'schedule-demo.php'],
                ['label' => 'Human handover', 'handover' => 'Prototype'],
            ],
        ],
        'pricing' => [
            'topic' => 'Pricing Question',
            'keywords' => ['price', 'pricing', 'cost', 'budget', 'package', 'quote', 'estimate', 'how much', 'rate'],
            'reply' => 'Pricing depends on scope, integrations, data sources, user roles, and launch support. A small prototype or assistant is usually scoped differently from a production automation or analytics dashboard. The quickest next step is a short demo request with your goal, timeline, and must-have features.',
            'actions' => [
                ['label' => 'Request quote context', 'handover' => 'Pricing Question'],
                ['label' => 'Schedule demo', 'route' => 'schedule-demo.php'],
                ['label' => 'Scope checklist', 'prompt' => 'What details do you need to estimate a project?'],
            ],
        ],
        'events' => [
            'topic' => 'Events',
            'keywords' => ['event', 'events', 'webinar', 'workshop', 'session', 'briefing', 'gallery', 'register'],
            'reply' => 'The events area shows workshops, product demos, dashboards, assistant demos, and automation handoff sessions. You can register interest or add an event to your calendar from the events page.',
            'actions' => [
                ['label' => 'Open events', 'route' => 'events.php'],
                ['label' => 'Workshop ideas', 'prompt' => 'What happens in an AI workflow workshop?'],
                ['label' => 'Schedule demo', 'route' => 'schedule-demo.php'],
            ],
        ],
        'security' => [
            'topic' => 'Security',
            'keywords' => ['security', 'secure', 'privacy', 'data', 'safe', 'compliance', 'risk', 'permission', 'admin'],
            'reply' => 'Security should be handled through careful data collection, protected admin access, validated forms, limited permissions, secure hosting, and a clear handover process. For production AI systems, the team should also review data retention, model access, audit logs, and user roles.',
            'actions' => [
                ['label' => 'Privacy page', 'route' => 'privacy.php'],
                ['label' => 'Discuss security', 'handover' => 'Security'],
                ['label' => 'View services', 'route' => 'services.php'],
            ],
        ],
        'contact' => [
            'topic' => 'Sales Representative',
            'keywords' => ['contact', 'sales', 'human', 'person', 'team', 'call me', 'email me', 'follow up', 'handover', 'representative'],
            'reply' => 'I can prepare a handover so the team sees what you asked about before they follow up. You can also use the contact form if you prefer to send a fuller project brief.',
            'actions' => [
                ['label' => 'Start handover', 'handover' => 'Sales Representative'],
                ['label' => 'Contact form', 'route' => 'contact.php'],
                ['label' => 'Schedule demo', 'route' => 'schedule-demo.php'],
            ],
        ],
    ];
}

function assistant_score_intent($text, array $intent) {
    $score = 0;
    foreach ($intent['keywords'] as $keyword) {
        if (strpos($text, assistant_lower($keyword)) !== false) {
            $score += strlen($keyword) > 8 ? 2 : 1;
        }
    }
    return $score;
}

function assistant_detect_intent($message) {
    $text = assistant_lower($message);

    if (preg_match('/\b(who are you|what are you|your name|who r u|what r u)\b/', $text)) {
        return [
            'key' => 'identity',
            'topic' => 'Virtual Assistant',
            'reply' => 'I am ai-solutions chatbot',
            'actions' => assistant_default_actions(),
        ];
    }

    if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening)\b/', $text)) {
        return [
            'key' => 'greeting',
            'topic' => 'Sales Representative',
            'reply' => 'Hi, I am AI-Solutions. I can help you choose an AI solution, understand pricing, explore events, book a demo, or prepare a human handover. What are you trying to improve?',
            'actions' => assistant_default_actions(),
        ];
    }

    if (preg_match('/\b(thanks|thank you|perfect|great|awesome)\b/', $text)) {
        return [
            'key' => 'thanks',
            'topic' => 'Sales Representative',
            'reply' => 'You are welcome. If you want, I can turn this into a demo request or a short handover summary for the team.',
            'actions' => assistant_default_actions(),
        ];
    }

    foreach (['pricing', 'demo', 'contact'] as $priority_key) {
        $intent = assistant_intents()[$priority_key];
        if (assistant_score_intent($text, $intent) > 0) {
            return [
                'key' => $priority_key,
                'topic' => $intent['topic'],
                'reply' => $intent['reply'],
                'actions' => $intent['actions'],
            ];
        }
    }

    $best_key = null;
    $best_score = 0;
    foreach (assistant_intents() as $key => $intent) {
        $score = assistant_score_intent($text, $intent);
        if ($score > $best_score) {
            $best_score = $score;
            $best_key = $key;
        }
    }

    if ($best_key !== null) {
        $intent = assistant_intents()[$best_key];
        return [
            'key' => $best_key,
            'topic' => $intent['topic'],
            'reply' => $intent['reply'],
            'actions' => $intent['actions'],
        ];
    }

    return [
        'key' => 'fallback',
        'topic' => 'Sales Representative',
        'reply' => 'I can help with AI assistants, workflow automation, analytics dashboards, prototypes, events, pricing, and human handover. Share the task, customer journey, or decision you want to improve, and I will suggest the best route.',
        'actions' => assistant_default_actions(),
    ];
}

function assistant_normalize_history($history) {
    if (!is_array($history)) {
        return [];
    }

    $clean = [];
    foreach (array_slice($history, -8) as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $role = (string) ($entry['role'] ?? $entry['type'] ?? 'user');
        $role = in_array($role, ['user', 'assistant', 'bot'], true) ? $role : 'user';
        $text = assistant_clean_text($entry['content'] ?? $entry['text'] ?? '', 500);
        if ($text === '') {
            continue;
        }
        $clean[] = [
            'role' => $role === 'bot' ? 'assistant' : $role,
            'content' => $text,
        ];
    }
    return $clean;
}

function assistant_site_context() {
    return 'AI-Solutions is a professional services website for secure AI assistants, workflow automation, analytics dashboards, and rapid prototypes. Key public pages are services.php, schedule-demo.php, events.php, contact.php, chatbot.php, privacy.php, and chatbot-handover.php. The assistant should be concise, helpful, realistic, and route users to demos or human handover when the question requires exact pricing, technical discovery, or follow-up.';
}

function assistant_http_post_json($url, array $payload, array $headers, $timeout) {
    $body = json_encode($payload);
    if ($body === false) {
        return null;
    }

    $header_lines = array_merge(['Content-Type: application/json'], $headers);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $header_lines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(4, $timeout),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($response === false || $status >= 400) {
            return null;
        }
        return $response;
    }

    if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
        return null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $header_lines),
            'content' => $body,
            'timeout' => $timeout,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }
    return $response;
}

// ---------------------------------------------------------------------------
// Claude (Anthropic Messages API) — the primary, most intelligent brain.
// Called over raw HTTPS because this project ships without Composer; the same
// hand-rolled cURL helper already used for the keyless fallback is reused here.
// Returns the reply text, or null so the caller can fall back gracefully.
// ---------------------------------------------------------------------------
function assistant_claude_reply($message, array $history, array $local) {
    $api_key = defined('ANTHROPIC_API_KEY') ? trim((string) ANTHROPIC_API_KEY) : '';
    if ($api_key === '') {
        return null;
    }

    $model = defined('ANTHROPIC_MODEL') ? trim((string) ANTHROPIC_MODEL) : 'claude-opus-4-8';
    if ($model === '') {
        $model = 'claude-opus-4-8';
    }
    $version = defined('ANTHROPIC_VERSION') ? (string) ANTHROPIC_VERSION : '2023-06-01';
    $timeout = defined('AI_ASSISTANT_TIMEOUT') ? max(4, min(30, (int) AI_ASSISTANT_TIMEOUT + 4)) : 12;

    $system = assistant_site_context()
        . ' The current question maps locally to the topic "' . $local['topic'] . '".'
        . ' You are the AI-Solutions website assistant. Be warm, concise, and genuinely helpful.'
        . ' Keep replies under 130 words and finish with one concrete next step (a page to open, a demo to book, or a human handover).'
        . ' Never invent a fixed price, and never claim a demo, booking, or handover has already been completed.'
        . ' When the visitor needs exact pricing or technical discovery, point them to a demo or human handover.';

    // The Messages API requires messages to start with "user" and to alternate
    // roles, so collapse consecutive same-role turns and trim any leading
    // assistant turn (e.g. the welcome message) before appending the new question.
    $messages = [];
    foreach ($history as $entry) {
        $role = (($entry['role'] ?? 'user') === 'assistant') ? 'assistant' : 'user';
        $content = assistant_clean_text($entry['content'] ?? '', 500);
        if ($content === '') {
            continue;
        }
        if (!empty($messages) && $messages[count($messages) - 1]['role'] === $role) {
            $messages[count($messages) - 1]['content'] .= "\n" . $content;
        } else {
            $messages[] = ['role' => $role, 'content' => $content];
        }
    }
    while (!empty($messages) && $messages[0]['role'] !== 'user') {
        array_shift($messages);
    }
    if (!empty($messages) && $messages[count($messages) - 1]['role'] === 'user') {
        $messages[count($messages) - 1]['content'] .= "\n" . $message;
    } else {
        $messages[] = ['role' => 'user', 'content' => $message];
    }

    $response = assistant_http_post_json('https://api.anthropic.com/v1/messages', [
        'model' => $model,
        'max_tokens' => 400,
        'system' => $system,
        'messages' => $messages,
    ], [
        'x-api-key: ' . $api_key,
        'anthropic-version: ' . $version,
    ], $timeout);

    if ($response === null) {
        return null;
    }

    $json = json_decode($response, true);
    if (!is_array($json) || (($json['type'] ?? '') === 'error')) {
        return null;
    }
    // A safety refusal returns 200 with stop_reason "refusal"; fall back quietly.
    if (($json['stop_reason'] ?? '') === 'refusal') {
        return null;
    }

    $text = '';
    if (isset($json['content']) && is_array($json['content'])) {
        foreach ($json['content'] as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                $text .= $block['text'] ?? '';
            }
        }
    }

    $text = assistant_clean_text($text, 900);
    return $text !== '' ? $text : null;
}

function assistant_external_reply($message, array $history, array $local) {
    $api_url = defined('AI_ASSISTANT_API_URL') ? trim((string) AI_ASSISTANT_API_URL) : '';
    if ($api_url === '') {
        return null;
    }

    $model = defined('AI_ASSISTANT_MODEL') ? trim((string) AI_ASSISTANT_MODEL) : '';
    $model = $model !== '' ? $model : 'openai-fast';
    $timeout = defined('AI_ASSISTANT_TIMEOUT') ? max(3, min(20, (int) AI_ASSISTANT_TIMEOUT)) : 8;

    $messages = [
        [
            'role' => 'system',
            'content' => assistant_site_context() . ' Use the local intent topic "' . $local['topic'] . '" as context. Never invent a fixed price or claim a booking was made. Keep answers under 130 words and include one practical next step.',
        ],
    ];

    foreach ($history as $entry) {
        $messages[] = $entry;
    }

    $messages[] = [
        'role' => 'user',
        'content' => $message,
    ];

    $headers = [];
    $api_key = defined('AI_ASSISTANT_API_KEY') ? trim((string) AI_ASSISTANT_API_KEY) : '';
    if ($api_key !== '') {
        $headers[] = 'Authorization: Bearer ' . $api_key;
    }

    $response = assistant_http_post_json($api_url, [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.45,
        'max_tokens' => 220,
    ], $headers, $timeout);

    if ($response === null) {
        return null;
    }

    $json = json_decode($response, true);
    if (!is_array($json)) {
        return null;
    }

    $text = $json['choices'][0]['message']['content'] ?? null;
    if (!$text && isset($json['output'][0]['content'][0]['text'])) {
        $text = $json['output'][0]['content'][0]['text'];
    }
    if (!$text && isset($json['text'])) {
        $text = $json['text'];
    }

    $text = assistant_clean_text($text, 900);
    return $text !== '' ? $text : null;
}

function assistant_reply_payload($message, $history = []) {
    $message = assistant_clean_text($message, 1000);
    $history = assistant_normalize_history($history);
    $local = assistant_detect_intent($message);

    // Prefer Claude, then the keyless LLM, then the built-in rule-based answer.
    $reply = assistant_claude_reply($message, $history, $local);
    if (!$reply && $local['key'] !== 'identity') {
        $reply = assistant_external_reply($message, $history, $local);
    }
    $source = $reply ? 'api' : 'local';
    if (!$reply) {
        $reply = $local['reply'];
    }

    return [
        'reply' => $reply,
        'topic' => $local['topic'],
        'actions' => $local['actions'],
        'source' => $source,
    ];
}
