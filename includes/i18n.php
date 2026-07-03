<?php
require_once __DIR__ . '/functions.php';

// ---------------------------------------------------------------------
// Lightweight internationalisation scaffold.
// English is the source language; Nepali is included as a second locale
// to demonstrate the mechanism. Add locales/strings as the site grows.
// ---------------------------------------------------------------------

function ai_lang_labels() {
    return [
        'en' => 'EN',
        'ne' => 'ने',
    ];
}

function ai_lang_strings() {
    return [
        'en' => [
            'nav_home' => 'Home',
            'nav_solutions' => 'Solutions',
            'nav_events' => 'Events',
            'nav_articles' => 'Articles',
            'nav_contact' => 'Contact Us',
            'nav_demo' => 'Schedule Demo',
            'skip_to_content' => 'Skip to content',
        ],
        'ne' => [
            'nav_home' => 'गृह',
            'nav_solutions' => 'समाधानहरू',
            'nav_events' => 'कार्यक्रमहरू',
            'nav_articles' => 'लेखहरू',
            'nav_contact' => 'सम्पर्क गर्नुहोस्',
            'nav_demo' => 'डेमो समय तय गर्नुहोस्',
            'skip_to_content' => 'सामग्रीमा जानुहोस्',
        ],
    ];
}

function ai_lang_available() {
    return array_keys(ai_lang_strings());
}

// Persist a language choice from ?lang=xx (must run before any HTML output).
function ai_lang_set() {
    start_secure_session();
    if (isset($_GET['lang']) && in_array($_GET['lang'], ai_lang_available(), true)) {
        $_SESSION['lang'] = $_GET['lang'];
        if (!headers_sent()) {
            setcookie('ai_lang', $_GET['lang'], time() + 60 * 60 * 24 * 180, '/', '', false, true);
        }
    }
}

function ai_lang_current() {
    $available = ai_lang_available();
    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], $available, true)) {
        return $_SESSION['lang'];
    }
    if (!empty($_COOKIE['ai_lang']) && in_array($_COOKIE['ai_lang'], $available, true)) {
        return $_COOKIE['ai_lang'];
    }
    return 'en';
}

// Translate a key for the active language, falling back to English then the key.
function t($key) {
    $strings = ai_lang_strings();
    $lang = ai_lang_current();
    return $strings[$lang][$key] ?? $strings['en'][$key] ?? $key;
}

// ---------------------------------------------------------------------
// Phrase-based translation. English is the source: tr('English phrase')
// returns the Nepali translation when the active language is Nepali, and
// the original English otherwise (or when no translation is registered).
// This lets every visible string and data-catalog value be translated by
// wrapping it once, e.g. e(tr($solution['title'])).
// ---------------------------------------------------------------------
function ai_lang_phrases() {
    static $phrases = null;
    if ($phrases !== null) {
        return $phrases;
    }

    $phrases = [
        // ---- Navigation / shared ----
        'Home' => 'गृह',
        'Solutions' => 'समाधानहरू',
        'Events' => 'कार्यक्रमहरू',
        'Articles' => 'लेखहरू',
        'Contact Us' => 'सम्पर्क गर्नुहोस्',
        'Schedule Demo' => 'डेमो समय तय गर्नुहोस्',
        'Try Assistant' => 'सहायक प्रयोग गर्नुहोस्',
        'Book Demo' => 'डेमो बुक गर्नुहोस्',
        'Book a Demo' => 'डेमो बुक गर्नुहोस्',
        'Privacy Policy' => 'गोपनीयता नीति',
        'Terms of Service' => 'सेवाका सर्तहरू',
        'Company' => 'कम्पनी',
        'Services' => 'सेवाहरू',
        'Clear' => 'खाली गर्नुहोस्',
        'Cancel' => 'रद्द गर्नुहोस्',
        'Send' => 'पठाउनुहोस्',
        'Next' => 'अर्को',
        'All' => 'सबै',

        // ---- Footer ----
        'Secure AI assistants, workflow automation, analytics, and product prototypes built around measurable operational progress.' => 'नापजोख गर्न सकिने सञ्चालन प्रगतिमा आधारित सुरक्षित AI सहायक, कार्यप्रवाह स्वचालन, एनालिटिक्स, र उत्पादन प्रोटोटाइपहरू।',
        'Start a project' => 'परियोजना सुरु गर्नुहोस्',
        'Schedule demo' => 'डेमो समय तय गर्नुहोस्',
        'Location' => 'स्थान',
        'Open map' => 'नक्सा खोल्नुहोस्',
        'Cookie preferences' => 'कुकी प्राथमिकताहरू',
        'AI-Solutions uses a small analytics cookie to understand page visits and improve conversion paths.' => 'AI-Solutions ले पृष्ठ भ्रमण बुझ्न र रूपान्तरण मार्ग सुधार गर्न एउटा सानो एनालिटिक्स कुकी प्रयोग गर्छ।',
        'Accept' => 'स्वीकार गर्नुहोस्',
        'Decline' => 'अस्वीकार गर्नुहोस्',

        // ---- Chatbot widget / page ----
        'AI-Solutions Assistant' => 'AI-Solutions सहायक',
        'Ready to help' => 'सहयोगका लागि तयार',
        'Hi, I am AI-Solutions. Ask about services, pricing, demos, automation, or human handover.' => 'नमस्ते, म AI-Solutions हुँ। सेवा, मूल्य, डेमो, स्वचालन, वा मानव हस्तान्तरणबारे सोध्नुहोस्।',
        'Hi, I am AI-Solutions. Tell me your goal and I will suggest the best AI solution, next step, or human handover.' => 'नमस्ते, म AI-Solutions हुँ। तपाईंको लक्ष्य भन्नुहोस् र म उत्तम AI समाधान, अर्को चरण, वा मानव हस्तान्तरण सुझाव दिनेछु।',
        'Find Fit' => 'उपयुक्त खोज्नुहोस्',
        'Pricing' => 'मूल्य',
        'Human Handover' => 'मानव हस्तान्तरण',
        'Type your message...' => 'आफ्नो सन्देश टाइप गर्नुहोस्...',
        'Virtual assistant' => 'भर्चुअल सहायक',
        'Meet AI-Solutions, your 3D AI service concierge.' => 'AI-Solutions लाई भेट्नुहोस्, तपाईंको 3D AI सेवा कन्सियर्ज।',
        'AI-Solutions helps visitors understand AI solutions, compare next steps, book demos, and move complex requests to a human team member with useful context attached.' => 'AI-Solutions ले आगन्तुकहरूलाई AI समाधान बुझ्न, अर्को चरण तुलना गर्न, डेमो बुक गर्न, र जटिल अनुरोधहरू उपयोगी सन्दर्भसहित मानव टोली सदस्यमा सार्न मद्दत गर्छ।',
        'visitor guidance' => 'आगन्तुक मार्गदर्शन',
        'smart pathways' => 'स्मार्ट मार्गहरू',
        'human handover' => 'मानव हस्तान्तरण',
        'Clear Chat' => 'च्याट खाली गर्नुहोस्',
        'Automation' => 'स्वचालन',
        'Ask about services, demos, automation, pricing...' => 'सेवा, डेमो, स्वचालन, मूल्यबारे सोध्नुहोस्...',
        'Request Human Handover' => 'मानव हस्तान्तरण अनुरोध गर्नुहोस्',
        'View Services' => 'सेवाहरू हेर्नुहोस्',
        'Designed for handover' => 'हस्तान्तरणका लागि डिजाइन गरिएको',
        'Friendly automation with a clean human path.' => 'सफा मानव मार्गसहितको मैत्रीपूर्ण स्वचालन।',
        'AI-Solutions keeps the conversation focused, suggests useful next steps, and prepares a summary when a visitor wants the team to follow up.' => 'AI-Solutions ले संवादलाई केन्द्रित राख्छ, उपयोगी अर्को चरण सुझाव दिन्छ, र आगन्तुकले टोलीलाई फलोअप गराउन चाहँदा सारांश तयार गर्छ।',
        'Service matching' => 'सेवा मिलान',
        'Recommends assistants, automation, dashboards, or prototypes.' => 'सहायक, स्वचालन, ड्यासबोर्ड, वा प्रोटोटाइप सिफारिस गर्छ।',
        'Smart routing' => 'स्मार्ट रुटिङ',
        'Moves visitors toward demos, events, sales, or contact paths.' => 'आगन्तुकहरूलाई डेमो, कार्यक्रम, बिक्री, वा सम्पर्क मार्गतर्फ लैजान्छ।',
        'Context capture' => 'सन्दर्भ क्याप्चर',
        'Summarizes the chat before handover.' => 'हस्तान्तरण अघि च्याट सारांश गर्छ।',

        // ---- Home page ----
        'Secure AI delivery for growing teams' => 'बढ्दो टोलीहरूका लागि सुरक्षित AI डेलिभरी',
        'AI that earns its place in your' => 'तपाईंको कार्यप्रवाहमा आफ्नो स्थान',
        'workflow' => 'कमाउने AI',
        'AI-Solutions designs assistants, automation systems, and analytics dashboards that help teams respond faster, reduce manual work, and make sharper decisions.' => 'AI-Solutions ले सहायक, स्वचालन प्रणाली, र एनालिटिक्स ड्यासबोर्डहरू डिजाइन गर्छ जसले टोलीहरूलाई छिटो प्रतिक्रिया दिन, म्यानुअल काम घटाउन, र चनाखो निर्णय लिन मद्दत गर्छ।',
        'Explore Services' => 'सेवाहरू हेर्नुहोस्',
        'assistant coverage' => 'सहायक कभरेज',
        'faster lead routing' => 'छिटो लिड रुटिङ',
        'team controlled' => 'टोली नियन्त्रित',
        'Operations Overview' => 'सञ्चालन सारांश',
        'Inquiries' => 'सोधपुछ',
        'Handled' => 'समाधान भएको',
        'Avg reply' => 'औसत जवाफ',
        'faster response' => 'छिटो प्रतिक्रिया',
        'What we build' => 'हामी के बनाउँछौं',
        'AI systems that feel useful from the first interaction.' => 'पहिलो अन्तर्क्रियादेखि नै उपयोगी महसुस हुने AI प्रणालीहरू।',
        'Every solution is designed around a business workflow: capture the request, automate the repeatable work, surface the right data, and hand complex cases to people with context intact.' => 'प्रत्येक समाधान व्यापार कार्यप्रवाहका वरिपरि डिजाइन गरिएको छ: अनुरोध समात्नुहोस्, दोहोरिने काम स्वचालित गर्नुहोस्, सही डेटा देखाउनुहोस्, र जटिल केसहरू सन्दर्भसहित मानिसहरूलाई सुम्पनुहोस्।',
        'Customer AI Assistants' => 'ग्राहक AI सहायक',
        'Guided support, qualification, demo routing, and handover flows that keep customer conversations moving.' => 'ग्राहक संवादलाई निरन्तर अघि बढाउने निर्देशित सहयोग, योग्यता, डेमो रुटिङ, र हस्तान्तरण प्रवाह।',
        'Workflow Automation' => 'कार्यप्रवाह स्वचालन',
        'Streamlined intake, approvals, notifications, and operations support built to reduce manual follow-up.' => 'म्यानुअल फलोअप घटाउन बनाइएको सरलीकृत इन्टेक, स्वीकृति, सूचना, र सञ्चालन सहयोग।',
        'Analytics Dashboards' => 'एनालिटिक्स ड्यासबोर्ड',
        'Business-ready views that turn inquiries, demos, leads, and service data into practical decisions.' => 'सोधपुछ, डेमो, लिड, र सेवा डेटालाई व्यावहारिक निर्णयमा बदल्ने व्यापार-तयार दृश्यहरू।',
        'AI Product Prototypes' => 'AI उत्पादन प्रोटोटाइप',
        'Focused proof-of-concepts that help teams test ideas, validate workflows, and plan the next build.' => 'टोलीहरूलाई विचार परीक्षण गर्न, कार्यप्रवाह प्रमाणित गर्न, र अर्को निर्माण योजना बनाउन मद्दत गर्ने केन्द्रित प्रुफ-अफ-कन्सेप्ट।',
        'Delivery approach' => 'डेलिभरी दृष्टिकोण',
        'Professional, secure, and built around your real operations.' => 'व्यावसायिक, सुरक्षित, र तपाईंको वास्तविक सञ्चालनका वरिपरि निर्मित।',
        'AI should make the business clearer, not more chaotic. We start with your user journey, design the right automation layer, and give your team a controlled follow-up workspace.' => 'AI ले व्यापारलाई अझ स्पष्ट बनाउनुपर्छ, अराजक होइन। हामी तपाईंको प्रयोगकर्ता यात्राबाट सुरु गर्छौं, सही स्वचालन तह डिजाइन गर्छौं, र तपाईंको टोलीलाई नियन्त्रित फलोअप कार्यक्षेत्र दिन्छौं।',
        'Start a Project' => 'परियोजना सुरु गर्नुहोस्',
        'Discover' => 'खोज',
        'Map goals, risks, users, and operational bottlenecks.' => 'लक्ष्य, जोखिम, प्रयोगकर्ता, र सञ्चालन अवरोधहरूको नक्सा बनाउनुहोस्।',
        'Prototype' => 'प्रोटोटाइप',
        'Launch focused AI flows and dashboards quickly.' => 'केन्द्रित AI प्रवाह र ड्यासबोर्डहरू छिटो सुरु गर्नुहोस्।',
        'Harden' => 'सुदृढ',
        'Improve security, validation, team workflows, and data quality.' => 'सुरक्षा, प्रमाणीकरण, टोली कार्यप्रवाह, र डेटा गुणस्तर सुधार गर्नुहोस्।',
        'Scale' => 'विस्तार',
        'Extend automation into more teams and customer journeys.' => 'थप टोली र ग्राहक यात्रामा स्वचालन विस्तार गर्नुहोस्।',
        'News and insights' => 'समाचार र अन्तर्दृष्टि',
        'Recent thinking from the AI-Solutions team.' => 'AI-Solutions टोलीको हालैको सोच।',
        'Short, practical notes for teams planning AI assistants, workflow automation, analytics, and prototypes.' => 'AI सहायक, कार्यप्रवाह स्वचालन, एनालिटिक्स, र प्रोटोटाइप योजना बनाउने टोलीहरूका लागि छोटो, व्यावहारिक टिप्पणीहरू।',
        'View Articles' => 'लेखहरू हेर्नुहोस्',
        'Visitor feedback' => 'आगन्तुक प्रतिक्रिया',
        'Customer ratings' => 'ग्राहक मूल्याङ्कन',
        'Browse recent visitor feedback and add your own rating from the Contact Us page.' => 'हालैको आगन्तुक प्रतिक्रिया हेर्नुहोस् र सम्पर्क पृष्ठबाट आफ्नो मूल्याङ्कन थप्नुहोस्।',
        'Rate Your Visit' => 'आफ्नो भ्रमण मूल्याङ्कन गर्नुहोस्',
        'Average confidence' => 'औसत विश्वास',
        'Ready when you are' => 'तपाईं तयार हुँदा हामी पनि तयार',
        'Bring us the workflow that costs your team time.' => 'तपाईंको टोलीको समय खाने कार्यप्रवाह हामीलाई ल्याउनुहोस्।',
        'We will help turn it into a cleaner AI-assisted system with measurable business value.' => 'हामी त्यसलाई नापजोख गर्न सकिने व्यापार मूल्यसहितको सफा AI-सहायतित प्रणालीमा बदल्न मद्दत गर्नेछौं।',

        // ---- Solutions page ----
        'Industry-specific AI solutions for practical teams.' => 'व्यावहारिक टोलीहरूका लागि उद्योग-विशिष्ट AI समाधानहरू।',
        'Filter by industry or capability to see where assistants, automation, analytics, and prototypes can fit into real work.' => 'सहायक, स्वचालन, एनालिटिक्स, र प्रोटोटाइप वास्तविक काममा कहाँ मिल्छन् हेर्न उद्योग वा क्षमताअनुसार फिल्टर गर्नुहोस्।',
        'Retail' => 'खुद्रा',
        'Healthcare' => 'स्वास्थ्य सेवा',
        'Education' => 'शिक्षा',
        'Assistant' => 'सहायक',
        'Analytics' => 'एनालिटिक्स',
        'No ratings yet' => 'अहिलेसम्म कुनै मूल्याङ्कन छैन',
        'Rate this' => 'यसलाई मूल्याङ्कन गर्नुहोस्',
        'Case studies' => 'केस स्टडीहरू',
        'Past industry projects and measurable results.' => 'विगतका उद्योग परियोजना र नापजोख गर्न सकिने परिणामहरू।',
        'Real-world examples of how AI-Solutions solved a problem and the impact it delivered.' => 'AI-Solutions ले समस्या कसरी समाधान गर्‍यो र यसले ल्याएको प्रभावका वास्तविक उदाहरणहरू।',
        'Objective' => 'उद्देश्य',
        'Challenge' => 'चुनौती',
        'Solution' => 'समाधान',
        'Rate a solution' => 'समाधान मूल्याङ्कन गर्नुहोस्',
        'Used one of our solutions? Leave a review.' => 'हाम्रो कुनै समाधान प्रयोग गर्नुभयो? समीक्षा छोड्नुहोस्।',
        'Reviews are checked by the AI-Solutions admin team before they appear on the solution, so ratings stay authentic.' => 'मूल्याङ्कन प्रामाणिक रहोस् भनेर समीक्षाहरू समाधानमा देखिनुअघि AI-Solutions एड्मिन टोलीले जाँच्छ।',
        'Thank you. Your review has been submitted and will appear once the admin team approves it.' => 'धन्यवाद। तपाईंको समीक्षा पेस भएको छ र एड्मिन टोलीले स्वीकृत गरेपछि देखिनेछ।',
        'Website' => 'वेबसाइट',
        'Solution *' => 'समाधान *',
        'Select a solution' => 'समाधान छान्नुहोस्',
        'Your Name *' => 'तपाईंको नाम *',
        'Email Address *' => 'इमेल ठेगाना *',
        'Rating *' => 'मूल्याङ्कन *',
        'Select rating' => 'मूल्याङ्कन छान्नुहोस्',
        '5 - Excellent' => '५ - उत्कृष्ट',
        '4 - Great' => '४ - राम्रो',
        '3 - Good' => '३ - ठीक',
        '2 - Fair' => '२ - सामान्य',
        '1 - Needs work' => '१ - सुधार चाहिन्छ',
        'Your Review *' => 'तपाईंको समीक्षा *',
        'Submit Review' => 'समीक्षा पेस गर्नुहोस्',
        'Customer feedback' => 'ग्राहक प्रतिक्रिया',
        'Solutions are shaped around real visitor and customer signals.' => 'समाधानहरू वास्तविक आगन्तुक र ग्राहक सङ्केतका वरिपरि आकार दिइन्छन्।',
        'Admin analytics, customer inquiries, event registrations, chatbot handovers, and visitor ratings help the team refine what customers need most.' => 'एड्मिन एनालिटिक्स, ग्राहक सोधपुछ, कार्यक्रम दर्ता, च्याटबोट हस्तान्तरण, र आगन्तुक मूल्याङ्कनले ग्राहकलाई सबैभन्दा बढी के चाहिन्छ भनेर टोलीलाई परिष्कृत गर्न मद्दत गर्छ।',
        'Start an Inquiry' => 'सोधपुछ सुरु गर्नुहोस्',
        'Leave Feedback' => 'प्रतिक्रिया दिनुहोस्',
        ' rated ' => ' ले मूल्याङ्कन गर्‍यो ',
        'Feedback-ready' => 'प्रतिक्रिया-तयार',
        'Customer ratings appear here once visitors submit feedback.' => 'आगन्तुकले प्रतिक्रिया पेस गरेपछि ग्राहक मूल्याङ्कन यहाँ देखिन्छ।',
        'Next step' => 'अर्को चरण',
        'See how the solution fits your workflow.' => 'समाधान तपाईंको कार्यप्रवाहमा कसरी मिल्छ हेर्नुहोस्।',

        // ---- Solutions catalog ----
        'Retail support assistant' => 'खुद्रा सहयोग सहायक',
        'Guide shoppers, answer common service questions, qualify leads, and hand complex requests to staff.' => 'किनमेल गर्नेहरूलाई मार्गदर्शन, सामान्य सेवा प्रश्नको जवाफ, लिड योग्य बनाउनुहोस्, र जटिल अनुरोध कर्मचारीलाई सुम्पनुहोस्।',
        'Product guidance' => 'उत्पादन मार्गदर्शन',
        'Lead capture' => 'लिड क्याप्चर',
        'Human handover' => 'मानव हस्तान्तरण',
        'Try assistant' => 'सहायक प्रयोग गर्नुहोस्',
        'Healthcare intake automation' => 'स्वास्थ्य सेवा इन्टेक स्वचालन',
        'Organise non-clinical requests, route follow-ups, and reduce repeated manual admin steps.' => 'गैर-क्लिनिकल अनुरोधहरू व्यवस्थित गर्नुहोस्, फलोअप रुट गर्नुहोस्, र दोहोरिने म्यानुअल एड्मिन चरण घटाउनुहोस्।',
        'Secure intake' => 'सुरक्षित इन्टेक',
        'Team routing' => 'टोली रुटिङ',
        'Status tracking' => 'स्थिति ट्र्याकिङ',
        'Request info' => 'जानकारी अनुरोध गर्नुहोस्',
        'Professional services dashboard' => 'व्यावसायिक सेवा ड्यासबोर्ड',
        'Track inquiries, demo demand, conversion quality, and delivery signals in one admin view.' => 'एउटै एड्मिन दृश्यमा सोधपुछ, डेमो माग, रूपान्तरण गुणस्तर, र डेलिभरी सङ्केत ट्र्याक गर्नुहोस्।',
        'Inquiry KPIs' => 'सोधपुछ KPIs',
        'Demand trends' => 'माग प्रवृत्ति',
        'Exportable reports' => 'निर्यातयोग्य रिपोर्ट',
        'Book demo' => 'डेमो बुक गर्नुहोस्',
        'Education event assistant' => 'शिक्षा कार्यक्रम सहायक',
        'Promote sessions, answer registration questions, and capture RSVP interest for events.' => 'सत्रहरू प्रवर्द्धन गर्नुहोस्, दर्ता प्रश्नको जवाफ दिनुहोस्, र कार्यक्रमका लागि RSVP रुचि समात्नुहोस्।',
        'Event guidance' => 'कार्यक्रम मार्गदर्शन',
        'RSVP capture' => 'RSVP क्याप्चर',
        'Calendar links' => 'पात्रो लिङ्क',
        'View events' => 'कार्यक्रमहरू हेर्नुहोस्',
        'Operations workflow automation' => 'सञ्चालन कार्यप्रवाह स्वचालन',
        'Move repeated approvals, notifications, and task-routing steps out of spreadsheets and inboxes.' => 'दोहोरिने स्वीकृति, सूचना, र कार्य-रुटिङ चरणहरू स्प्रेडसिट र इनबक्सबाट बाहिर ल्याउनुहोस्।',
        'Process mapping' => 'प्रक्रिया म्यापिङ',
        'Notifications' => 'सूचनाहरू',
        'Audit trail' => 'अडिट ट्रेल',
        'Start project' => 'परियोजना सुरु गर्नुहोस्',
        'AI prototype sprint' => 'AI प्रोटोटाइप स्प्रिन्ट',
        'Build a focused proof-of-concept to validate an AI workflow before a larger rollout.' => 'ठूलो रोलआउट अघि AI कार्यप्रवाह प्रमाणित गर्न केन्द्रित प्रुफ-अफ-कन्सेप्ट बनाउनुहोस्।',
        'MVP scope' => 'MVP स्कोप',
        'Clickable flows' => 'क्लिक गर्न मिल्ने प्रवाह',
        'Iteration plan' => 'पुनरावृत्ति योजना',
        'Plan sprint' => 'स्प्रिन्ट योजना बनाउनुहोस्',

        // ---- Case studies ----
        'Cutting retail support backlog with an AI assistant' => 'AI सहायकले खुद्रा सहयोग ब्याकलग घटाउँदै',
        'Reduce repetitive "where is my order" and product questions overwhelming a small support team.' => 'सानो सहयोग टोलीलाई थिचिरहेका दोहोरिने "मेरो अर्डर कहाँ छ" र उत्पादन प्रश्नहरू घटाउने।',
        'Two agents handled 400+ weekly chats, so complex cases waited hours behind routine questions.' => 'दुई एजेन्टले साप्ताहिक ४००+ च्याट सम्हाल्थे, त्यसैले जटिल केसहरू सामान्य प्रश्नहरूको पछाडि घण्टौं पर्खन्थे।',
        'Deployed a retail support assistant that answered common questions, qualified leads, and handed only complex cases to staff with full context.' => 'सामान्य प्रश्नको जवाफ दिने, लिड योग्य बनाउने, र जटिल केस मात्र पूर्ण सन्दर्भसहित कर्मचारीलाई सुम्पने खुद्रा सहयोग सहायक तैनात गरियो।',
        '62% of chats resolved without an agent; average first response down from 4 hours to under 2 minutes.' => '६२% च्याट एजेन्टविना समाधान भयो; औसत पहिलो प्रतिक्रिया ४ घण्टाबाट २ मिनेटभन्दा कममा झर्‍यो।',
        'Streamlining healthcare intake admin' => 'स्वास्थ्य सेवा इन्टेक एड्मिन सरल बनाउँदै',
        'Remove manual data re-entry from a non-clinical patient intake and referral workflow.' => 'गैर-क्लिनिकल बिरामी इन्टेक र रेफरल कार्यप्रवाहबाट म्यानुअल डेटा पुनः प्रविष्टि हटाउने।',
        'Staff re-keyed the same request details across three systems, causing delays and errors.' => 'कर्मचारीले एउटै अनुरोध विवरण तीन प्रणालीमा पुनः टाइप गर्थे, जसले ढिलाइ र त्रुटि निम्त्याउँथ्यो।',
        'Automated intake captured structured requests once and routed each follow-up to the right team with status tracking.' => 'स्वचालित इन्टेकले संरचित अनुरोध एक पटक समात्यो र प्रत्येक फलोअप स्थिति ट्र्याकिङसहित सही टोलीमा रुट गर्‍यो।',
        'Manual admin time reduced by ~11 hours per week and intake errors fell by 40%.' => 'म्यानुअल एड्मिन समय हप्तामा ~११ घण्टा घट्यो र इन्टेक त्रुटि ४०% ले घट्यो।',
        'A single analytics view for a services firm' => 'सेवा फर्मका लागि एकल एनालिटिक्स दृश्य',
        'Professional services' => 'व्यावसायिक सेवा',
        'Give leadership one place to see inquiry demand, demo interest, and conversion quality.' => 'नेतृत्वलाई सोधपुछ माग, डेमो रुचि, र रूपान्तरण गुणस्तर हेर्न एउटै ठाउँ दिने।',
        'Data lived in spreadsheets and inboxes, so monthly reporting took a full day to assemble.' => 'डेटा स्प्रेडसिट र इनबक्समा थियो, त्यसैले मासिक रिपोर्टिङ तयार गर्न पूरै दिन लाग्थ्यो।',
        'A professional services dashboard consolidated inquiries, demos, and visitor analytics with exportable reports.' => 'व्यावसायिक सेवा ड्यासबोर्डले सोधपुछ, डेमो, र आगन्तुक एनालिटिक्सलाई निर्यातयोग्य रिपोर्टसहित एकीकृत गर्‍यो।',
        'Monthly reporting time cut from ~8 hours to 20 minutes, with weekly demand trends now visible at a glance.' => 'मासिक रिपोर्टिङ समय ~८ घण्टाबाट २० मिनेटमा झर्‍यो, साप्ताहिक माग प्रवृत्ति अब एकै नजरमा देखिन्छ।',

        // ---- Events page ----
        'Events and insights' => 'कार्यक्रम र अन्तर्दृष्टि',
        'Join practical sessions that show AI working inside real operations.' => 'वास्तविक सञ्चालनभित्र AI काम गरेको देखाउने व्यावहारिक सत्रहरूमा सहभागी हुनुहोस्।',
        'Customers can view upcoming technical demonstrations, register interest, and help AI-Solutions understand demand for assistants, automation, dashboards, and prototypes.' => 'ग्राहकहरूले आगामी प्राविधिक प्रदर्शन हेर्न, रुचि दर्ता गर्न, र सहायक, स्वचालन, ड्यासबोर्ड, र प्रोटोटाइपको माग बुझ्न AI-Solutions लाई मद्दत गर्न सक्छन्।',
        'Upcoming events' => 'आगामी कार्यक्रमहरू',
        'Live sessions' => 'प्रत्यक्ष सत्रहरू',
        'Filter by solution area, reserve a seat, or add a session to your calendar.' => 'समाधान क्षेत्रअनुसार फिल्टर गर्नुहोस्, सिट आरक्षण गर्नुहोस्, वा सत्र आफ्नो पात्रोमा थप्नुहोस्।',
        'Completed' => 'सम्पन्न',
        'Open' => 'खुला',
        'Closed' => 'बन्द',
        'Add calendar' => 'पात्रोमा थप्नुहोस्',
        'No sessions match this filter.' => 'यो फिल्टरसँग कुनै सत्र मिल्दैन।',
        'Event snapshot' => 'कार्यक्रम स्न्यापसट',
        'Built for practical learning' => 'व्यावहारिक सिकाइका लागि बनाइएको',
        'Upcoming' => 'आगामी',
        'Tracks' => 'ट्र्याकहरू',
        'Online' => 'अनलाइन',
        'Delivery' => 'डेलिभरी',
        'Live demos' => 'प्रत्यक्ष डेमो',
        'Assistants, dashboards, and automation walkthroughs.' => 'सहायक, ड्यासबोर्ड, र स्वचालन वाकथ्रु।',
        'Team-ready' => 'टोली-तयार',
        'Sessions designed for managers, analysts, and operators.' => 'प्रबन्धक, विश्लेषक, र सञ्चालकका लागि डिजाइन गरिएका सत्रहरू।',
        'Actionable' => 'कार्ययोग्य',
        'Clear next steps after every promotional event.' => 'हरेक प्रवर्द्धनात्मक कार्यक्रमपछि स्पष्ट अर्को चरण।',
        'Reserve a seat' => 'सिट आरक्षण गर्नुहोस्',
        'Read' => 'पढ्नुहोस्',
        '5 ways AI transforms customer support' => 'AI ले ग्राहक सहयोग रूपान्तरण गर्ने ५ तरिका',
        'How smart intake, routing, and context-aware handover improve customer experience without overwhelming staff.' => 'स्मार्ट इन्टेक, रुटिङ, र सन्दर्भ-सचेत हस्तान्तरणले कर्मचारीलाई नथिची ग्राहक अनुभव कसरी सुधार्छ।',
        'Read articles' => 'लेखहरू पढ्नुहोस्',
        'Gallery' => 'ग्यालरी',
        'Moments from AI-Solutions events and delivery sessions.' => 'AI-Solutions कार्यक्रम र डेलिभरी सत्रका क्षणहरू।',
        'Explore professional workshop, dashboard, assistant, automation, and prototype scenes that show how technical solution events translate into real operational outcomes.' => 'प्राविधिक समाधान कार्यक्रमहरू कसरी वास्तविक सञ्चालन नतिजामा परिणत हुन्छन् देखाउने व्यावसायिक कार्यशाला, ड्यासबोर्ड, सहायक, स्वचालन, र प्रोटोटाइप दृश्यहरू हेर्नुहोस्।',
        'Prototype showcase' => 'प्रोटोटाइप प्रदर्शनी',
        'Interactive AI product demos' => 'अन्तरक्रियात्मक AI उत्पादन डेमो',
        'Live prototype walkthroughs help teams see how an idea can become a usable customer or operations tool.' => 'प्रत्यक्ष प्रोटोटाइप वाकथ्रुले टोलीहरूलाई विचार कसरी उपयोगी ग्राहक वा सञ्चालन उपकरण बन्न सक्छ हेर्न मद्दत गर्छ।',
        'Workshop' => 'कार्यशाला',
        'Workflow discovery' => 'कार्यप्रवाह खोज',
        'Teams map service journeys and uncover the highest-value places for automation.' => 'टोलीहरूले सेवा यात्राको नक्सा बनाउँछन् र स्वचालनका लागि सबैभन्दा उच्च-मूल्य ठाउँहरू पत्ता लगाउँछन्।',
        'Dashboard' => 'ड्यासबोर्ड',
        'Decision-ready reporting' => 'निर्णय-तयार रिपोर्टिङ',
        'Dashboards turn inquiries, bookings, and visitor activity into clear operational signals.' => 'ड्यासबोर्डले सोधपुछ, बुकिङ, र आगन्तुक गतिविधिलाई स्पष्ट सञ्चालन सङ्केतमा बदल्छ।',
        'Assistant demo' => 'सहायक डेमो',
        'Customer support flows' => 'ग्राहक सहयोग प्रवाह',
        'AI assistant sessions show how intake, qualification, and handover can work together.' => 'AI सहायक सत्रहरूले इन्टेक, योग्यता, र हस्तान्तरण कसरी सँगै काम गर्न सक्छन् देखाउँछन्।',
        'Operations handoff' => 'सञ्चालन ह्यान्डअफ',
        'Automation demos focus on repeatable workflows, approval paths, and team follow-up.' => 'स्वचालन डेमोहरू दोहोरिने कार्यप्रवाह, स्वीकृति मार्ग, र टोली फलोअपमा केन्द्रित हुन्छन्।',
        'Join our events' => 'हाम्रा कार्यक्रममा सहभागी हुनुहोस्',
        'Register for a promotional event.' => 'प्रवर्द्धनात्मक कार्यक्रमका लागि दर्ता गर्नुहोस्।',
        'Your details help the admin team measure event demand by country, company, and solution interest.' => 'तपाईंको विवरणले एड्मिन टोलीलाई देश, कम्पनी, र समाधान रुचिअनुसार कार्यक्रम माग नाप्न मद्दत गर्छ।',
        'That session has already happened. Choose one of the upcoming events below.' => 'त्यो सत्र पहिले नै भइसकेको छ। तलका आगामी कार्यक्रमहरूमध्ये एउटा छान्नुहोस्।',
        'Full Name *' => 'पूरा नाम *',
        'Phone Number *' => 'फोन नम्बर *',
        'Company Name *' => 'कम्पनीको नाम *',
        'Country *' => 'देश *',
        'Event *' => 'कार्यक्रम *',
        'Select event' => 'कार्यक्रम छान्नुहोस्',
        'Interest Area *' => 'रुचि क्षेत्र *',
        'Select interest' => 'रुचि छान्नुहोस्',
        'Questions or Notes' => 'प्रश्न वा टिप्पणी',
        'I agree to be contacted about this AI-Solutions event.' => 'म यो AI-Solutions कार्यक्रमबारे सम्पर्क गरिन सहमत छु।',
        'Join Event' => 'कार्यक्रममा सहभागी हुनुहोस्',
        'Book Seat' => 'सिट बुक गर्नुहोस्',

        // ---- Event catalog ----
        'AI for Business Webinar' => 'व्यापारका लागि AI वेबिनार',
        'A practical walkthrough of how AI streamlines support, operations, and reporting.' => 'AI ले सहयोग, सञ्चालन, र रिपोर्टिङ कसरी सरल बनाउँछ भन्ने व्यावहारिक वाकथ्रु।',
        'Virtual Assistant Live Demo' => 'भर्चुअल सहायक प्रत्यक्ष डेमो',
        'See customer guidance, lead capture, and human handover flows in action.' => 'ग्राहक मार्गदर्शन, लिड क्याप्चर, र मानव हस्तान्तरण प्रवाह काममा हेर्नुहोस्।',
        'Automation Workflow Clinic' => 'स्वचालन कार्यप्रवाह क्लिनिक',
        'Learn how intake, approvals, and notifications can move from manual follow-up to reliable automation.' => 'इन्टेक, स्वीकृति, र सूचना म्यानुअल फलोअपबाट भरपर्दो स्वचालनमा कसरी सर्न सक्छ सिक्नुहोस्।',
        'Analytics Dashboard Live Build' => 'एनालिटिक्स ड्यासबोर्ड प्रत्यक्ष निर्माण',
        'Watch inquiry, demo, and service data become a practical decision dashboard.' => 'सोधपुछ, डेमो, र सेवा डेटा व्यावहारिक निर्णय ड्यासबोर्ड बनेको हेर्नुहोस्।',
        'Prototype Planning Workshop' => 'प्रोटोटाइप योजना कार्यशाला',
        'Plan a small, testable AI prototype before committing to a larger build.' => 'ठूलो निर्माणमा प्रतिबद्ध हुनुअघि सानो, परीक्षणयोग्य AI प्रोटोटाइप योजना गर्नुहोस्।',

        // ---- Interest options / demo types ----
        'Virtual Assistant' => 'भर्चुअल सहायक',
        'Data Analytics' => 'डेटा एनालिटिक्स',
        'AI Product Prototyping' => 'AI उत्पादन प्रोटोटाइपिङ',
        'Software Assistance' => 'सफ्टवेयर सहायता',
        'Sales Representative' => 'बिक्री प्रतिनिधि',
        'Full Consultation' => 'पूर्ण परामर्श',

        // ---- Contact page ----
        'Tell us what you want AI to improve.' => 'AI ले के सुधार्नुपर्छ हामीलाई भन्नुहोस्।',
        'No customer account is required. Share the workflow, customer journey, or reporting gap you want to fix.' => 'कुनै ग्राहक खाता आवश्यक छैन। तपाईंले समाधान गर्न चाहेको कार्यप्रवाह, ग्राहक यात्रा, वा रिपोर्टिङ अन्तर साझा गर्नुहोस्।',
        'Project intake' => 'परियोजना इन्टेक',
        'Useful details help us respond with a sharper plan.' => 'उपयोगी विवरणले हामीलाई अझ चनाखो योजनासहित जवाफ दिन मद्दत गर्छ।',
        'Describe the process, user group, and outcome you care about. We will review it and follow up with next steps.' => 'तपाईंलाई महत्त्वपूर्ण लाग्ने प्रक्रिया, प्रयोगकर्ता समूह, र नतिजा वर्णन गर्नुहोस्। हामी यसको समीक्षा गरी अर्को चरणसहित फलोअप गर्नेछौं।',
        'Solution Interest *' => 'समाधान रुचि *',
        'Security Check:' => 'सुरक्षा जाँच:',
        'Job Title *' => 'पदको नाम *',
        'Job Details / Message *' => 'कार्य विवरण / सन्देश *',
        'I agree that AI-Solutions may store and use my details to respond to this inquiry.' => 'म AI-Solutions ले यो सोधपुछको जवाफ दिन मेरो विवरण भण्डारण र प्रयोग गर्न सक्ने कुरामा सहमत छु।',
        'Submit Inquiry' => 'सोधपुछ पेस गर्नुहोस्',
        'Rate your visit' => 'आफ्नो भ्रमण मूल्याङ्कन गर्नुहोस्',
        'Tell us how the experience felt.' => 'अनुभव कस्तो लाग्यो हामीलाई भन्नुहोस्।',
        'Your feedback goes to the AI-Solutions team for review and improvement.' => 'तपाईंको प्रतिक्रिया समीक्षा र सुधारका लागि AI-Solutions टोलीमा जान्छ।',
        'Thank you. Your rating has been received and will help us improve the visitor experience.' => 'धन्यवाद। तपाईंको मूल्याङ्कन प्राप्त भएको छ र यसले आगन्तुक अनुभव सुधार गर्न मद्दत गर्नेछ।',
        'Organization' => 'संस्था',
        'Role / Title' => 'भूमिका / पद',
        'Overall Rating *' => 'समग्र मूल्याङ्कन *',
        'What should we keep improving? *' => 'हामीले के सुधार्दै जानुपर्छ? *',
        'Submit Rating' => 'मूल्याङ्कन पेस गर्नुहोस्',
        'Excellent' => 'उत्कृष्ट',
        'Great' => 'राम्रो',
        'Good' => 'ठीक',
        'Fair' => 'सामान्य',
        'Needs work' => 'सुधार चाहिन्छ',

        // ---- Schedule demo page ----
        'See the assistant, automation, and analytics flow together.' => 'सहायक, स्वचालन, र एनालिटिक्स सँगै बग्ने हेर्नुहोस्।',
        'Choose a preferred date and time. We will confirm the session and tailor the walkthrough to your goals.' => 'रुचाइएको मिति र समय छान्नुहोस्। हामी सत्र पुष्टि गर्नेछौं र तपाईंको लक्ष्यअनुसार वाकथ्रु मिलाउनेछौं।',
        'Demo booking' => 'डेमो बुकिङ',
        'Pick the solution area you want to inspect first.' => 'तपाईंले पहिले निरीक्षण गर्न चाहेको समाधान क्षेत्र छान्नुहोस्।',
        'The more specific your notes, the more relevant the personalised demo will be.' => 'तपाईंको टिप्पणी जति विशिष्ट हुन्छ, व्यक्तिगत डेमो त्यति सान्दर्भिक हुनेछ।',
        'Preferred Date *' => 'रुचाइएको मिति *',
        'Preferred Time *' => 'रुचाइएको समय *',
        'Interested Solution *' => 'रुचि भएको समाधान *',
        'Select solution' => 'समाधान छान्नुहोस्',
        'Additional Notes' => 'थप टिप्पणी',
        'I agree to be contacted by AI-Solutions.' => 'म AI-Solutions द्वारा सम्पर्क गरिन सहमत छु।',

        // ---- Articles page ----
        'Articles and news' => 'लेख र समाचार',
        'Company updates and AI implementation insights.' => 'कम्पनी अपडेट र AI कार्यान्वयन अन्तर्दृष्टि।',
        'Read practical notes from AI-Solutions on assistants, workflow automation, analytics dashboards, and prototype planning.' => 'सहायक, कार्यप्रवाह स्वचालन, एनालिटिक्स ड्यासबोर्ड, र प्रोटोटाइप योजनाबारे AI-Solutions का व्यावहारिक टिप्पणीहरू पढ्नुहोस्।',
        'Search articles by topic or keyword' => 'विषय वा किवर्डअनुसार लेख खोज्नुहोस्',
        'Search' => 'खोज्नुहोस्',
        'result for' => 'परिणाम:',
        'results for' => 'परिणामहरू:',
        'Read article' => 'लेख पढ्नुहोस्',
        'No articles match that search. Try a different keyword.' => 'त्यो खोजसँग कुनै लेख मिल्दैन। अर्को किवर्ड प्रयास गर्नुहोस्।',
        'Stay connected' => 'जोडिइरहनुहोस्',
        'Have a workflow question for the next article?' => 'अर्को लेखका लागि कार्यप्रवाह प्रश्न छ?',
        'Send a Question' => 'प्रश्न पठाउनुहोस्',
        'View Events' => 'कार्यक्रमहरू हेर्नुहोस्',

        // ---- Article catalog ----
        'How AI support routing reduces follow-up delays' => 'AI सपोर्ट रुटिङले फलोअप ढिलाइ कसरी घटाउँछ',
        'Customer Experience' => 'ग्राहक अनुभव',
        'A practical look at intake, qualification, and human handover patterns for service teams.' => 'सेवा टोलीहरूका लागि इन्टेक, योग्यता, र मानव हस्तान्तरण ढाँचाहरूको व्यावहारिक दृष्टिकोण।',
        'Support teams lose the most time on triage: reading each message, deciding who should handle it, and re-typing the same answers. AI support routing tackles this by classifying and qualifying every inbound request the moment it arrives.' => 'सहयोग टोलीहरूले ट्रायजमा सबैभन्दा बढी समय गुमाउँछन्: हरेक सन्देश पढ्ने, कसले सम्हाल्ने निर्णय गर्ने, र उही जवाफ पुनः टाइप गर्ने। AI सपोर्ट रुटिङले प्रत्येक आगमन अनुरोधलाई आइपुग्ने बित्तिकै वर्गीकरण र योग्य बनाएर यो समस्या समाधान गर्छ।',
        'The pattern that works best keeps a human in the loop. The assistant answers routine questions instantly, captures structured details for everything else, and routes complex cases to the right person with full context attached.' => 'सबैभन्दा राम्रो काम गर्ने ढाँचाले मानिसलाई प्रक्रियामा राख्छ। सहायकले सामान्य प्रश्नको तुरुन्त जवाफ दिन्छ, बाँकी सबैका लागि संरचित विवरण समात्छ, र जटिल केसहरू पूर्ण सन्दर्भसहित सही व्यक्तिमा रुट गर्छ।',
        'Teams that adopt this see faster first responses and fewer dropped follow-ups, because nothing waits in a shared inbox without an owner. Start small: automate your three most common request types before expanding.' => 'यो अपनाउने टोलीहरूले छिटो पहिलो प्रतिक्रिया र कम छुटेका फलोअप देख्छन्, किनभने मालिकविना कुनै पनि कुरा साझा इनबक्समा पर्खंदैन। सानोबाट सुरु गर्नुहोस्: विस्तार गर्नुअघि तपाईंका तीन सबैभन्दा सामान्य अनुरोध प्रकार स्वचालित गर्नुहोस्।',
        'A readiness checklist for workflow automation' => 'कार्यप्रवाह स्वचालनका लागि तत्परता जाँचसूची',
        'Operations' => 'सञ्चालन',
        'The signals that show when a repeated manual process is ready for automation.' => 'दोहोरिने म्यानुअल प्रक्रिया स्वचालनका लागि कहिले तयार छ भनेर देखाउने सङ्केतहरू।',
        'Not every manual process should be automated first. The best early candidates are high-volume, rule-based, and stable, where the steps rarely change and mistakes are costly.' => 'हरेक म्यानुअल प्रक्रिया पहिले स्वचालित गर्नुहुँदैन। उत्तम सुरुवाती उम्मेदवारहरू उच्च-मात्रा, नियम-आधारित, र स्थिर हुन्छन्, जहाँ चरणहरू विरलै बदलिन्छन् र गल्ती महँगो हुन्छ।',
        'Before automating, document the current workflow end to end. If you cannot describe the steps clearly on paper, automation will simply make a confusing process faster and harder to debug.' => 'स्वचालित गर्नुअघि, हालको कार्यप्रवाह सुरुदेखि अन्त्यसम्म कागजात गर्नुहोस्। यदि तपाईं चरणहरू कागजमा स्पष्ट रूपमा वर्णन गर्न सक्नुहुन्न भने, स्वचालनले अलमलिएको प्रक्रियालाई छिटो र डिबग गर्न कठिन मात्र बनाउँछ।',
        'Use a short checklist: is the process repeated often, are the rules consistent, is the data already structured, and is there a clear owner? When most answers are yes, the process is ready.' => 'छोटो जाँचसूची प्रयोग गर्नुहोस्: के प्रक्रिया बारम्बार दोहोरिन्छ, के नियमहरू सुसंगत छन्, के डेटा पहिले नै संरचित छ, र के स्पष्ट मालिक छ? धेरैजसो जवाफ हो भएमा, प्रक्रिया तयार छ।',
        'Metrics every AI pilot dashboard should include' => 'हरेक AI पाइलट ड्यासबोर्डमा हुनुपर्ने मेट्रिक्स',
        'Track inquiries, demos, conversion points, and service quality before expanding a prototype.' => 'प्रोटोटाइप विस्तार गर्नुअघि सोधपुछ, डेमो, रूपान्तरण बिन्दु, र सेवा गुणस्तर ट्र्याक गर्नुहोस्।',
        'A pilot dashboard should answer one question: is this AI solution creating measurable value? Vanity metrics like total messages hide the answer, so focus on outcomes instead.' => 'पाइलट ड्यासबोर्डले एउटा प्रश्नको जवाफ दिनुपर्छ: के यो AI समाधानले नापजोख गर्न सकिने मूल्य सिर्जना गर्दैछ? कुल सन्देशजस्ता देखावटी मेट्रिक्सले जवाफ लुकाउँछन्, त्यसैले बरु नतिजामा केन्द्रित हुनुहोस्।',
        'Track inquiry volume and source, demo and conversion rates, time saved per task, and a quality signal such as customer rating or escalation rate. Together these show both adoption and impact.' => 'सोधपुछ मात्रा र स्रोत, डेमो र रूपान्तरण दर, प्रति कार्य बचेको समय, र ग्राहक मूल्याङ्कन वा वृद्धि दरजस्ता गुणस्तर सङ्केत ट्र्याक गर्नुहोस्। यी सँगै अपनाउने र प्रभाव दुवै देखाउँछन्।',
        'Keep the dashboard small enough to read in thirty seconds. If a metric never changes a decision, remove it and protect the signal that matters.' => 'ड्यासबोर्ड तीस सेकेन्डमा पढ्न सकिने गरी सानो राख्नुहोस्। यदि कुनै मेट्रिकले निर्णय कहिल्यै बदल्दैन भने, त्यसलाई हटाउनुहोस् र महत्त्वपूर्ण सङ्केत जोगाउनुहोस्।',
        'Keeping AI prototypes small enough to learn quickly' => 'छिटो सिक्न AI प्रोटोटाइपलाई सानो राख्ने',
        'Product' => 'उत्पादन',
        'How to define a focused AI prototype that proves value without becoming a long project.' => 'लामो परियोजना नबनी मूल्य प्रमाणित गर्ने केन्द्रित AI प्रोटोटाइप कसरी परिभाषित गर्ने।',
        'The goal of a prototype is learning, not completeness. A focused proof-of-concept that proves one workflow beats a broad build that proves nothing for months.' => 'प्रोटोटाइपको लक्ष्य सिकाइ हो, पूर्णता होइन। एउटा कार्यप्रवाह प्रमाणित गर्ने केन्द्रित प्रुफ-अफ-कन्सेप्टले महिनौंसम्म केही प्रमाणित नगर्ने फराकिलो निर्माणलाई जित्छ।',
        'Pick a single user journey, define what success looks like in numbers, and time-box the build. Everything outside that journey is a distraction until the core idea is validated.' => 'एउटै प्रयोगकर्ता यात्रा छान्नुहोस्, सफलता सङ्ख्यामा कस्तो देखिन्छ परिभाषित गर्नुहोस्, र निर्माणलाई समय-सीमा राख्नुहोस्। मूल विचार प्रमाणित नहुन्जेल त्यो यात्राबाहिरका सबै कुरा ध्यानभङ्ग हुन्।',
        'When the prototype answers its question, you have earned the right to expand. Iterate in short cycles and let real usage, not assumptions, decide what to build next.' => 'जब प्रोटोटाइपले आफ्नो प्रश्नको जवाफ दिन्छ, तपाईंले विस्तार गर्ने अधिकार कमाउनुभयो। छोटो चक्रमा पुनरावृत्ति गर्नुहोस् र अनुमानले होइन वास्तविक प्रयोगले अर्को के बनाउने निर्णय गरोस्।',

        // ---- Article detail ----
        'Back to Articles' => 'लेखहरूमा फर्कनुहोस्',
        'Talk to AI-Solutions' => 'AI-Solutions सँग कुरा गर्नुहोस्',
        'Article not found' => 'लेख फेला परेन',
        'That article may have moved.' => 'त्यो लेख सरेको हुन सक्छ।',
        'Browse all articles' => 'सबै लेख हेर्नुहोस्',

        // ---- Success page ----
        'Your inquiry has been saved successfully. Our team will review it and follow up.' => 'तपाईंको सोधपुछ सफलतापूर्वक सुरक्षित भयो। हाम्रो टोलीले समीक्षा गरी फलोअप गर्नेछ।',
        'Your demo request has been booked successfully. Our team will confirm the details.' => 'तपाईंको डेमो अनुरोध सफलतापूर्वक बुक भयो। हाम्रो टोलीले विवरण पुष्टि गर्नेछ।',
        'Your event registration has been saved successfully. Our team will send the joining details.' => 'तपाईंको कार्यक्रम दर्ता सफलतापूर्वक सुरक्षित भयो। हाम्रो टोलीले सहभागी हुने विवरण पठाउनेछ।',
        'Your chatbot handover request has been sent to our team.' => 'तपाईंको च्याटबोट हस्तान्तरण अनुरोध हाम्रो टोलीमा पठाइएको छ।',
        'Your rating has been received. Thank you for helping us improve the visitor experience.' => 'तपाईंको मूल्याङ्कन प्राप्त भयो। आगन्तुक अनुभव सुधार गर्न मद्दत गर्नुभएकोमा धन्यवाद।',
        'Your request has been saved successfully.' => 'तपाईंको अनुरोध सफलतापूर्वक सुरक्षित भयो।',
        'Request submitted' => 'अनुरोध पेस गरियो',
        'We received it.' => 'हामीले यो प्राप्त गर्‍यौं।',
        'Reference:' => 'सन्दर्भ:',
        'Back to Home' => 'गृहपृष्ठमा फर्कनुहोस्',

        // ---- Privacy page ----
        'Privacy policy' => 'गोपनीयता नीति',
        'How AI-Solutions handles visitor and inquiry data.' => 'AI-Solutions ले आगन्तुक र सोधपुछ डेटा कसरी ह्यान्डल गर्छ।',
        'This prototype stores only the information needed to respond to inquiries, manage events, run demos, and understand site performance.' => 'यो प्रोटोटाइपले सोधपुछको जवाफ दिन, कार्यक्रम व्यवस्थापन गर्न, डेमो चलाउन, र साइट प्रदर्शन बुझ्न आवश्यक जानकारी मात्र भण्डारण गर्छ।',
        'Data' => 'डेटा',
        'What we collect' => 'हामी के सङ्कलन गर्छौं',
        'Contact forms, demo bookings, event RSVPs, chatbot handovers, visitor feedback, and consent-based analytics events.' => 'सम्पर्क फारम, डेमो बुकिङ, कार्यक्रम RSVP, च्याटबोट हस्तान्तरण, आगन्तुक प्रतिक्रिया, र सहमति-आधारित एनालिटिक्स घटनाहरू।',
        'Use' => 'प्रयोग',
        'Why we use it' => 'हामी किन प्रयोग गर्छौं',
        'To respond to requests, improve services, measure conversions, and manage admin follow-up securely.' => 'अनुरोधको जवाफ दिन, सेवा सुधार्न, रूपान्तरण नाप्न, र एड्मिन फलोअप सुरक्षित रूपमा व्यवस्थापन गर्न।',
        'Rights' => 'अधिकार',
        'Your choices' => 'तपाईंका विकल्पहरू',
        'You can request correction, export, or deletion of personal data by contacting the AI-Solutions admin team.' => 'तपाईंले AI-Solutions एड्मिन टोलीलाई सम्पर्क गरेर व्यक्तिगत डेटाको सुधार, निर्यात, वा मेटाउन अनुरोध गर्न सक्नुहुन्छ।',
        'GDPR-oriented handling' => 'GDPR-उन्मुख ह्यान्डलिङ',
        'Retention and access are intentionally limited.' => 'भण्डारण र पहुँच जानाजानी सीमित गरिएको छ।',
        'Admin access is password protected, form submissions are validated, and analytics only runs when visitors accept cookies.' => 'एड्मिन पहुँच पासवर्डले सुरक्षित छ, फारम पेसहरू प्रमाणित हुन्छन्, र आगन्तुकले कुकी स्वीकार गरेमा मात्र एनालिटिक्स चल्छ।',
        'Retention' => 'भण्डारण',
        'Inquiry and event records should be reviewed monthly and removed when no longer needed.' => 'सोधपुछ र कार्यक्रम रेकर्डहरू मासिक रूपमा समीक्षा गरी आवश्यक नभएपछि हटाइनुपर्छ।',
        'Security' => 'सुरक्षा',
        'Production deployments should use HTTPS, strong admin credentials, MFA, and routine backups.' => 'उत्पादन डिप्लोयमेन्टले HTTPS, बलियो एड्मिन प्रमाण, MFA, र नियमित ब्याकअप प्रयोग गर्नुपर्छ।',
        'Cookies' => 'कुकीहरू',
        'The analytics cookie is optional and can be declined through the site banner.' => 'एनालिटिक्स कुकी वैकल्पिक हो र साइट ब्यानरमार्फत अस्वीकार गर्न सकिन्छ।',

        // ---- Terms page ----
        'Terms of service' => 'सेवाका सर्तहरू',
        'Responsible use of the AI-Solutions website.' => 'AI-Solutions वेबसाइटको जिम्मेवार प्रयोग।',
        'These terms explain how visitors should use the prototype website, forms, event RSVPs, and assistant experience.' => 'यी सर्तहरूले आगन्तुकले प्रोटोटाइप वेबसाइट, फारम, कार्यक्रम RSVP, र सहायक अनुभव कसरी प्रयोग गर्नुपर्छ भनेर व्याख्या गर्छन्।',
        'Website use' => 'वेबसाइट प्रयोग',
        'Use the forms and assistant for genuine business inquiries, event registrations, demo requests, and feedback.' => 'वास्तविक व्यापार सोधपुछ, कार्यक्रम दर्ता, डेमो अनुरोध, र प्रतिक्रियाका लागि फारम र सहायक प्रयोग गर्नुहोस्।',
        'Information accuracy' => 'जानकारी शुद्धता',
        'Visitors are responsible for providing accurate contact and company information when submitting forms.' => 'फारम पेस गर्दा सही सम्पर्क र कम्पनी जानकारी दिने जिम्मेवारी आगन्तुकको हो।',
        'Prototype limits' => 'प्रोटोटाइप सीमा',
        'Content is provided for demonstration and planning purposes. Production deployments require final legal, security, and hosting review.' => 'सामग्री प्रदर्शन र योजना उद्देश्यका लागि प्रदान गरिएको हो। उत्पादन डिप्लोयमेन्टलाई अन्तिम कानुनी, सुरक्षा, र होस्टिङ समीक्षा आवश्यक छ।',

        // ---- Server-side validation messages ----
        'Security check failed. Please try again.' => 'सुरक्षा जाँच असफल भयो। फेरि प्रयास गर्नुहोस्।',
        'Please enter a valid email address.' => 'कृपया मान्य इमेल ठेगाना प्रविष्ट गर्नुहोस्।',
        'Please select an upcoming event.' => 'कृपया आगामी कार्यक्रम छान्नुहोस्।',
        'Please select a valid interest area.' => 'कृपया मान्य रुचि क्षेत्र छान्नुहोस्।',
        'Please confirm consent to be contacted about the event.' => 'कृपया कार्यक्रमबारे सम्पर्क गरिन सहमति पुष्टि गर्नुहोस्।',
        'Please select a valid solution interest.' => 'कृपया मान्य समाधान रुचि छान्नुहोस्।',
        'Please answer the security check correctly.' => 'कृपया सुरक्षा जाँच सही जवाफ दिनुहोस्।',
        'Please confirm that AI-Solutions may store and use your details to respond.' => 'कृपया AI-Solutions ले जवाफ दिन तपाईंको विवरण भण्डारण र प्रयोग गर्न सक्ने पुष्टि गर्नुहोस्।',
        'Preferred date cannot be in the past.' => 'रुचाइएको मिति विगतको हुन सक्दैन।',
        'Please select a valid demo type.' => 'कृपया मान्य डेमो प्रकार छान्नुहोस्।',
        'Please confirm consent to be contacted.' => 'कृपया सम्पर्क गरिन सहमति पुष्टि गर्नुहोस्।',
    ];

    return $phrases;
}

// Translate a full English phrase to the active language.
function tr($english) {
    if (ai_lang_current() !== 'en') {
        $lang = ai_lang_current();
        if ($lang === 'ne') {
            $phrases = ai_lang_phrases();
            if (isset($phrases[$english])) {
                return $phrases[$english];
            }
        }
    }
    return $english;
}

// Build a switcher link to the current page in another language.
function ai_lang_switch_url($lang) {
    $path = strtok($_SERVER['REQUEST_URI'] ?? 'index.php', '?');
    return $path . '?lang=' . rawurlencode($lang);
}
