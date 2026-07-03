<?php
require_once __DIR__ . '/cms.php';

// DB-first: managed events from the CMS, with the hardcoded defaults below as
// fallback + first-run seed.
function ai_solutions_event_catalog() {
    return cms_catalog('event', 'ai_solutions_event_catalog_defaults');
}

function ai_solutions_event_catalog_defaults() {
    return [
        [
            'name' => 'AI for Business Webinar',
            'date' => '2026-06-03',
            'time' => '11:00 AM - 12:30 PM',
            'summary' => 'A practical walkthrough of how AI streamlines support, operations, and reporting.',
            'interest' => 'Software Assistance',
            'action' => 'Join Event',
        ],
        [
            'name' => 'Virtual Assistant Live Demo',
            'date' => '2026-06-10',
            'time' => '2:00 PM - 3:00 PM',
            'summary' => 'See customer guidance, lead capture, and human handover flows in action.',
            'interest' => 'Virtual Assistant',
            'action' => 'Book Seat',
        ],
        [
            'name' => 'Automation Workflow Clinic',
            'date' => '2026-06-24',
            'time' => '1:00 PM - 2:00 PM',
            'summary' => 'Learn how intake, approvals, and notifications can move from manual follow-up to reliable automation.',
            'interest' => 'Workflow Automation',
            'action' => 'Join Event',
        ],
        [
            'name' => 'Analytics Dashboard Live Build',
            'date' => '2026-07-08',
            'time' => '3:30 PM - 4:30 PM',
            'summary' => 'Watch inquiry, demo, and service data become a practical decision dashboard.',
            'interest' => 'Data Analytics',
            'action' => 'Book Seat',
        ],
        [
            'name' => 'Prototype Planning Workshop',
            'date' => '2026-07-22',
            'time' => '10:30 AM - 12:00 PM',
            'summary' => 'Plan a small, testable AI prototype before committing to a larger build.',
            'interest' => 'AI Product Prototyping',
            'action' => 'Join Event',
        ],
    ];
}

function ai_solutions_event_names() {
    return array_map(fn($event) => $event['name'], ai_solutions_event_catalog());
}

// DB-first gallery cards shown on the Events page, with hardcoded defaults.
function ai_solutions_gallery_catalog() {
    return cms_catalog('gallery', 'ai_solutions_gallery_catalog_defaults');
}

function ai_solutions_gallery_catalog_defaults() {
    return [
        ['badge' => 'Prototype showcase', 'title' => 'Interactive AI product demos', 'caption' => 'Live prototype walkthroughs help teams see how an idea can become a usable customer or operations tool.', 'image' => 'assets/images/gallery-prototype.jpg', 'alt' => 'AI product prototype showcase with a presenter demonstrating analytics on a large display'],
        ['badge' => 'Workshop', 'title' => 'Workflow discovery', 'caption' => 'Teams map service journeys and uncover the highest-value places for automation.', 'image' => 'assets/images/gallery-workshop.jpg', 'alt' => 'AI workflow workshop with business professionals reviewing process maps around a table'],
        ['badge' => 'Dashboard', 'title' => 'Decision-ready reporting', 'caption' => 'Dashboards turn inquiries, bookings, and visitor activity into clear operational signals.', 'image' => 'assets/images/gallery-dashboard.jpg', 'alt' => 'Analytics dashboard demonstration in a modern meeting room'],
        ['badge' => 'Assistant demo', 'title' => 'Customer support flows', 'caption' => 'AI assistant sessions show how intake, qualification, and handover can work together.', 'image' => 'assets/images/gallery-assistant.jpg', 'alt' => 'Consultant demonstrating an AI assistant flow to a customer support manager'],
        ['badge' => 'Automation', 'title' => 'Operations handoff', 'caption' => 'Automation demos focus on repeatable workflows, approval paths, and team follow-up.', 'image' => 'assets/images/gallery-automation.jpg', 'alt' => 'Operations team reviewing workflow automation launch steps in a modern operations hub'],
    ];
}

function ai_solutions_interest_options() {
    return [
        'Virtual Assistant',
        'Workflow Automation',
        'Data Analytics',
        'AI Product Prototyping',
        'Software Assistance',
        'Sales Representative',
    ];
}

function event_date_parts($date) {
    $timestamp = strtotime($date);
    return [
        'month' => strtoupper(date('M', $timestamp)),
        'day' => date('d', $timestamp),
        'year' => date('Y', $timestamp),
    ];
}

function event_has_finished($event, $today = null) {
    $today = $today ?: date('Y-m-d');
    return strcmp($event['date'], $today) < 0;
}

function event_display_date($date) {
    $timestamp = strtotime($date);
    return date('M j, Y', $timestamp);
}
