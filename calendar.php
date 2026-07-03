<?php
require_once 'includes/functions.php';
require_once 'includes/event-data.php';

$event_name = trim((string) ($_GET['event'] ?? ''));
$event = null;
foreach (ai_solutions_event_catalog() as $candidate) {
    if ($candidate['name'] === $event_name) {
        $event = $candidate;
        break;
    }
}

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

[$start_label, $end_label] = array_pad(array_map('trim', explode('-', $event['time'], 2)), 2, '');
$start = strtotime($event['date'] . ' ' . $start_label);
$end = $end_label !== '' ? strtotime($event['date'] . ' ' . $end_label) : strtotime('+1 hour', $start);
$uid = md5($event['name'] . $event['date']) . '@ai-solutions.local';
$filename = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $event['name']), '-')) . '.ics';

$ics = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//AI-Solutions//Events//EN',
    'BEGIN:VEVENT',
    'UID:' . $uid,
    'DTSTAMP:' . gmdate('Ymd\THis\Z'),
    'DTSTART:' . gmdate('Ymd\THis\Z', $start),
    'DTEND:' . gmdate('Ymd\THis\Z', $end),
    'SUMMARY:' . str_replace([',', ';'], ['\,', '\;'], $event['name']),
    'DESCRIPTION:' . str_replace([',', ';'], ['\,', '\;'], $event['summary']),
    'LOCATION:Online',
    'END:VEVENT',
    'END:VCALENDAR',
];

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo implode("\r\n", $ics);
exit;
