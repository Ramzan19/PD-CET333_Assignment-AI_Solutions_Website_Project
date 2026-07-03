<?php
require_once __DIR__ . '/cms.php';

// DB-first: managed articles from the CMS, falling back to the hardcoded
// defaults below (which also seed the CMS on first use).
function ai_solutions_article_catalog() {
    return cms_catalog('article', 'ai_solutions_article_catalog_defaults');
}

function ai_solutions_article_catalog_defaults() {
    return [
        [
            'slug' => 'ai-support-routing',
            'title' => 'How AI support routing reduces follow-up delays',
            'category' => 'Customer Experience',
            'date' => '2026-05-28',
            'summary' => 'A practical look at intake, qualification, and human handover patterns for service teams.',
            'keywords' => 'AI support routing, customer support automation, human handover, service desk AI',
            'body' => [
                'Most support teams do not have a quality problem. They have a routing problem. A message arrives, sits in a shared inbox, gets read by two or three people, and finally lands with someone who has to re-read the whole thread before they can even begin to help. By the time the customer hears back, the easy win — a fast, confident first response — has already been lost. AI support routing exists to close that gap, and when it is set up well it changes the economics of an entire service desk.',
                ['h' => 'Where the time actually goes'],
                'If you watch a support agent for an hour, very little of their time is spent solving genuinely hard problems. Most of it is triage: opening tickets, deciding who should own them, copying details into another system, and re-typing answers to questions they have answered a hundred times before. None of that work is visible to the customer, and none of it is why you hired skilled people. It is exactly the kind of repetitive, rules-based effort that an assistant can absorb.',
                'AI support routing attacks this directly. The moment a request arrives, the assistant reads it, classifies the intent, checks for the details a human will need, and decides whether it can answer immediately or should be handed to a person. Routine questions get an instant, accurate reply. Everything else is enriched with structured context and sent to the right queue, so the human who picks it up starts from understanding rather than from scratch.',
                ['h' => 'Keep a human firmly in the loop'],
                'The pattern that works best is not full automation — it is assisted handover. The assistant should be confident about the things it genuinely knows (opening hours, order status, how to reset a password, what a product does) and humble about everything else. When a request is ambiguous, emotional, or high-value, the right behaviour is to capture the details cleanly and escalate, not to guess.',
                'A good handover carries three things with it: a short summary of what the customer wants, the structured fields the team needs to act (account, order number, urgency), and the full transcript for anyone who wants the detail. That single design choice — never hand over an empty ticket — is what removes the second round of back-and-forth that usually eats another day.',
                ['h' => 'A realistic rollout'],
                'You do not need to automate everything on day one. The teams that succeed start narrow and expand once they trust the results:',
                ['list' => [
                    'Pick your three highest-volume request types and automate only those first.',
                    'Measure first-response time and escalation rate before and after, so you can prove the change.',
                    'Review every escalated conversation for the first two weeks to tune the assistant\'s confidence.',
                    'Only then widen the scope to the next set of request types.',
                ]],
                'Teams that adopt this approach consistently report faster first responses and far fewer dropped follow-ups, because nothing waits in a shared inbox without an owner. The assistant is not replacing the team — it is giving every customer a fast, useful first reply and giving the team the context to finish the job well.',
                ['h' => 'The takeaway'],
                'Routing is the unglamorous backbone of good support. Get it right and everything downstream improves: response times fall, agents spend their hours on work that needs a human, and customers stop repeating themselves. Start with one workflow, prove the value with numbers, and let the results justify the next step.',
            ],
        ],
        [
            'slug' => 'automation-readiness',
            'title' => 'A readiness checklist for workflow automation',
            'category' => 'Operations',
            'date' => '2026-05-18',
            'summary' => 'The signals that show when a repeated manual process is ready for automation.',
            'keywords' => 'workflow automation, process automation readiness, operations efficiency',
            'body' => [
                'Automation projects rarely fail because the technology was not capable. They fail because the wrong process was automated, or a messy process was automated before it was understood. The good news is that you can tell, before you write a line of logic, whether a workflow is ready. This article is a practical checklist for making that call.',
                ['h' => 'Not every process should go first'],
                'There is a strong temptation to automate the most painful process — the one everyone complains about. But pain is not the same as readiness. The best early candidates are high-volume, rule-based, and stable: the steps rarely change, the decisions follow clear logic, and the cost of a mistake is high enough to justify the effort. A process you run twice a year, or one where every case is a judgement call, is a poor place to start even if it is annoying.',
                ['h' => 'Document before you automate'],
                'The single most valuable exercise is to write the current workflow down, end to end, exactly as it happens today — including the awkward exceptions people handle by instinct. If you cannot describe the steps clearly on paper, automation will not fix that. It will simply make a confusing process run faster and become much harder to debug when something goes wrong.',
                'Mapping the process also surfaces the hidden decisions. Almost every "simple" workflow has a branch that an experienced person handles without thinking: "if the order is over a certain value, a manager approves it." Those branches are where automation projects quietly break, and writing them down is how you catch them early.',
                ['h' => 'The readiness checklist'],
                'Before committing to build, run the process through these questions. The more you answer yes, the more ready it is:',
                ['list' => [
                    'Is the process repeated often enough that automating it saves meaningful time?',
                    'Are the rules consistent, or does every case need human judgement?',
                    'Is the data already structured, or trapped in free-text and attachments?',
                    'Is there a single clear owner who can approve how it should behave?',
                    'Can you describe the success and failure cases in plain language?',
                    'Is there a safe fallback when the automation is unsure?',
                ]],
                'When most of these are yes, you have a strong candidate. When several are no, that is not a dead end — it is a signal to tidy the process first. Often the act of cleaning up a workflow so it can be automated delivers value on its own, before any software is built.',
                ['h' => 'Start small and keep a fallback'],
                'Even a ready process should launch narrowly. Automate the common, confident path first and route anything unusual to a person. That way the automation earns trust on the cases it handles well, and the edge cases stay safe in human hands until you choose to bring them in. Readiness is not a single yes-or-no — it is knowing exactly which slice of the work is safe to hand over first.',
            ],
        ],
        [
            'slug' => 'dashboard-metrics',
            'title' => 'Metrics every AI pilot dashboard should include',
            'category' => 'Analytics',
            'date' => '2026-05-07',
            'summary' => 'Track inquiries, demos, conversion points, and service quality before expanding a prototype.',
            'keywords' => 'AI analytics dashboard, pilot metrics, conversion tracking, KPIs',
            'body' => [
                'When a team launches an AI pilot, the most important question is also the simplest: is this creating measurable value? It is surprisingly easy to lose sight of that question behind a wall of impressive-looking numbers. A good pilot dashboard is designed to answer it honestly, in under a minute, to someone who was not in the room when the project started.',
                ['h' => 'Beware vanity metrics'],
                'Total messages handled, total users, total interactions — these feel like progress, but they rarely change a decision. A chatbot can handle ten thousand messages and still be quietly useless if none of them moved a customer closer to a resolution or a sale. Vanity metrics grow on their own and reassure everyone while telling you nothing. The discipline of a good dashboard is to leave them out.',
                ['h' => 'The metrics that matter'],
                'Focus instead on outcomes and quality. A small, honest set of numbers will tell you far more than a crowded screen:',
                ['list' => [
                    'Inquiry volume and source — how much demand there is, and where it comes from.',
                    'Conversion rate — how many conversations lead to a demo, a booking, or a sale.',
                    'Time saved per task — the practical efficiency the automation actually delivers.',
                    'Escalation rate — how often the assistant correctly hands off to a human.',
                    'A quality signal — customer rating or a sampled review of real transcripts.',
                ]],
                'Together these show both adoption (are people using it?) and impact (is it helping?). The pairing matters: high usage with low conversion means people are engaging but not getting what they need; low usage with high conversion means the few who find it love it, and your job is distribution, not product.',
                ['h' => 'Make it readable in thirty seconds'],
                'A dashboard that takes ten minutes to interpret will not be looked at. Keep it small enough to read at a glance, and put the one or two numbers that decide whether the pilot continues at the very top. Everything else is supporting detail. A useful test: for each metric on the screen, ask "what decision does this change?" If the answer is "none," remove it. Protecting the signal is more valuable than displaying more data.',
                ['h' => 'From pilot to production'],
                'The same dashboard that proves a pilot will later run the live service, so build it to grow. Add trend lines once you have a few weeks of history, segment by customer type once volume allows, and keep a sample of real conversations next to the numbers — the qualitative detail is what stops a metric from being misread. Measure what matters, show it plainly, and let the data, not the enthusiasm, decide what to build next.',
            ],
        ],
        [
            'slug' => 'prototype-scope',
            'title' => 'Keeping AI prototypes small enough to learn quickly',
            'category' => 'Product',
            'date' => '2026-04-24',
            'summary' => 'How to define a focused AI prototype that proves value without becoming a long project.',
            'keywords' => 'AI prototype, proof of concept, MVP scope, product validation',
            'body' => [
                'The purpose of a prototype is to learn something specific, as quickly and cheaply as possible. That sounds obvious, yet most AI prototypes quietly drift into becoming small products — gathering features, edge cases, and polish — long before anyone has confirmed the core idea is even worth building. A disciplined prototype trades completeness for speed of learning, and that trade is almost always the right one.',
                ['h' => 'Learning, not completeness'],
                'A focused proof-of-concept that proves one workflow end to end beats a broad build that demonstrates ten features but proves nothing for months. The question a prototype must answer is narrow: will this approach work, for this user, on this task, well enough to matter? Everything that does not help answer that question is, for now, a distraction.',
                ['h' => 'Define success in numbers first'],
                'Before building anything, write down what success looks like in concrete terms. "The assistant resolves the top five questions without a human at least eighty percent of the time." "A user can book a demo in under a minute." A numeric target does two things: it tells you when to stop, and it protects you from the moving goalposts that make prototypes run forever. If you cannot state the target, you are not ready to build — you are still exploring the problem, and that is a different activity.',
                ['h' => 'Scope ruthlessly'],
                'Pick a single user journey and build only that. Resist the pull of the second use case, the admin screen, the integration that "we will need eventually." A simple rule keeps a prototype honest:',
                ['list' => [
                    'One user, one journey, one clearly stated outcome.',
                    'A fixed time box — usually one to three weeks, not open-ended.',
                    'Real data or real users wherever possible, so the result is trustworthy.',
                    'A written list of everything you are deliberately leaving out.',
                ]],
                'That last point matters more than it looks. Naming what you are not building turns scope creep from an accident into a conscious decision someone has to defend.',
                ['h' => 'Earn the right to expand'],
                'When the prototype answers its question, you have earned the right to grow it — and only then. Iterate in short cycles, let real usage rather than assumptions decide the next step, and be willing to throw the prototype away if it taught you the idea does not work. A prototype that cheaply proves an idea is a failure is a success: it saved you the much larger cost of finding out later. Small, fast, and focused is not a compromise. For learning, it is the point.',
            ],
        ],
        [
            'slug' => 'choosing-first-ai-use-case',
            'title' => 'How to choose your first AI use case',
            'category' => 'Strategy',
            'date' => '2026-04-10',
            'summary' => 'A simple framework for picking an AI project that is valuable, viable, and safe to get wrong.',
            'keywords' => 'AI strategy, first AI project, use case selection, AI adoption',
            'body' => [
                'The hardest part of an AI programme is often the very first decision: what to build first. Choose well and you earn momentum, budget, and trust. Choose badly and a promising programme stalls on a project that was too ambitious, too risky, or too invisible to matter. This article offers a simple framework for making that first choice with confidence.',
                ['h' => 'Three questions that filter most ideas'],
                'Before falling in love with any idea, run it through three filters. They are deliberately blunt, because the goal is to eliminate the wrong projects quickly:',
                ['list' => [
                    'Is it valuable? Would success noticeably help the business, in time saved, revenue, or customer experience?',
                    'Is it viable? Can current AI genuinely do this task well, with the data you actually have?',
                    'Is it safe to get wrong? When the system makes a mistake, can you catch and recover from it cheaply?',
                ]],
                'A strong first project scores well on all three. It is tempting to chase the highest-value idea regardless of the other two, but a high-value project that is not viable, or where errors are catastrophic, is exactly how early AI efforts get a bad reputation internally.',
                ['h' => 'Favour reversible, observable work'],
                'For a first project, prefer tasks where mistakes are visible and reversible. Drafting a reply that a human approves is far safer than sending it automatically. Suggesting a routing decision is safer than enforcing it. Recommending an action is safer than taking it. Keeping a person in the loop on the first project is not a lack of ambition — it is how you build the trust that lets you remove the human later, once the system has earned it.',
                ['h' => 'Start where the data already lives'],
                'AI is only as good as the information it can see. A use case that depends on clean, structured data you already collect will move far faster than one that requires a six-month data-cleanup project before it can even begin. When two ideas are otherwise equal, pick the one whose data is already in good shape — you will see results in weeks rather than quarters.',
                ['h' => 'Make the win visible'],
                'Finally, choose something whose success can be seen and measured. An internal efficiency gain that nobody outside the team notices is a weak flagship, even if the numbers are good. A project that visibly improves a customer experience, or frees a team from work everyone hates, creates advocates — and advocates are what fund the next project. Valuable, viable, safe to get wrong, and visible: get those four right on your first use case, and the second one becomes much easier to start.',
            ],
        ],
        [
            'slug' => 'human-handover-done-right',
            'title' => 'Human handover, done right',
            'category' => 'Customer Experience',
            'date' => '2026-03-26',
            'summary' => 'Why the moment an AI assistant passes a conversation to a person decides the whole experience.',
            'keywords' => 'human handover, AI escalation, customer support, assisted service',
            'body' => [
                'Every AI assistant will, at some point, need to hand a conversation to a human. How it does that — the handover — quietly determines whether customers experience the assistant as helpful or as an obstacle they have to get past. A brilliant assistant with a clumsy handover feels worse than no assistant at all. This article is about getting that moment right.',
                ['h' => 'Know when to hand over'],
                'The first skill is recognising the moment. An assistant should escalate when a request is ambiguous, emotionally charged, high-value, or simply outside what it can confidently handle. The failure mode to avoid is the confident wrong answer: it is far better to say "let me bring in a colleague who can help with this properly" than to guess and erode trust. Train the assistant to be sure about what it knows and quick to escalate everything else.',
                ['h' => 'Never hand over an empty ticket'],
                'The cardinal rule of handover is that the human should never have to start from scratch. When a conversation is escalated, the person who receives it should immediately see three things:',
                ['list' => [
                    'A short summary of what the customer wants, in plain language.',
                    'The structured details the team needs to act — account, order, urgency, history.',
                    'The full transcript, available for anyone who wants the complete context.',
                ]],
                'This is the single change that removes the most frustrating part of assisted service: being asked to repeat everything you just typed. When the handover carries context, the customer feels continuity, not a reset.',
                ['h' => 'Be honest about the transition'],
                'Customers do not mind talking to an assistant, and they do not mind being passed to a person. What they mind is being misled about which is which. Make the transition clear and warm: tell the customer a human is taking over, set a realistic expectation of timing, and reassure them that the context has been carried across. Honesty here costs nothing and buys a great deal of goodwill.',
                ['h' => 'Close the loop'],
                'The handover does not end when the human replies — it ends when the system learns from it. Every escalation is a signal: a question the assistant could not answer, a workflow that needs work, a gap worth closing. Review escalations regularly and feed what you learn back into the assistant, and over time it handles more on its own and hands over only what genuinely needs a person. Done well, handover is not the assistant admitting defeat. It is the assistant and the team working as one, with the customer never feeling the seam between them.',
            ],
        ],
    ];
}

function ai_solutions_featured_articles($limit = 3) {
    return array_slice(ai_solutions_article_catalog(), 0, max(1, (int) $limit));
}

function ai_solutions_find_article($slug) {
    $slug = (string) $slug;
    foreach (ai_solutions_article_catalog() as $article) {
        if ($article['slug'] === $slug) {
            return $article;
        }
    }
    return null;
}

// Simple case-insensitive search across title, summary, category, and keywords.
function ai_solutions_search_articles($query) {
    $query = trim((string) $query);
    if ($query === '') {
        return ai_solutions_article_catalog();
    }

    $needle = mb_strtolower($query);
    return array_values(array_filter(ai_solutions_article_catalog(), function ($article) use ($needle) {
        $haystack = mb_strtolower(
            $article['title'] . ' ' . $article['summary'] . ' ' . $article['category'] . ' ' . ($article['keywords'] ?? '')
        );
        return mb_strpos($haystack, $needle) !== false;
    }));
}
