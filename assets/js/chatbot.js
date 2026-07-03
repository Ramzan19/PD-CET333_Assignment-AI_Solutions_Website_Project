const menuButton = document.querySelector('.mobile-menu-btn');

if (menuButton) {
  menuButton.addEventListener('click', () => {
    const isOpen = document.body.classList.toggle('nav-open');
    menuButton.setAttribute('aria-expanded', String(isOpen));
  });
}

const assistantHistories = new WeakMap();

const assistantConfig = {
  endpoint: document.querySelector('meta[name="assistant-endpoint"]')?.content || 'chatbot-api.php',
  csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
};

const defaultActions = [
  { label: 'Find Fit', prompt: 'Which AI solution fits my business?' },
  { label: 'Pricing', prompt: 'How much does an AI solution cost?' },
  { label: 'Schedule Demo', route: 'schedule-demo.php' },
  { label: 'Human Handover', handover: 'Sales Representative' },
];

const knowledgeBase = [
  {
    id: 'services',
    topic: 'Software Assistance',
    keywords: ['service', 'services', 'software', 'solution', 'solutions', 'build', 'recommend'],
    text: 'We build AI assistants, workflow automation, analytics dashboards, and rapid prototypes.',
    actions: [
      { label: 'View Services', route: 'services.php' },
      { label: 'Schedule Demo', route: 'schedule-demo.php' },
      { label: 'Speak to Sales', handover: 'Software Assistance' },
    ],
  },
  {
    id: 'demo',
    topic: 'Schedule Demo',
    keywords: ['demo', 'schedule', 'book', 'walkthrough', 'meeting', 'consultation'],
    text: 'You can book a guided walkthrough and share the workflow you want to improve.',
    actions: [
      { label: 'Open Demo Form', route: 'schedule-demo.php' },
      { label: 'Speak to Sales', handover: 'Sales Representative' },
    ],
  },
  {
    id: 'automation',
    topic: 'Software Assistance',
    keywords: ['automation', 'workflow', 'manual', 'approval', 'notification', 'operations', 'process'],
    text: 'We can automate intake, approvals, follow-ups, notifications, and reporting steps.',
    actions: [
      { label: 'View Services', route: 'services.php' },
      { label: 'Talk to Team', handover: 'Software Assistance' },
    ],
  },
  {
    id: 'assistant',
    topic: 'Virtual Assistant',
    keywords: ['assistant', 'chatbot', 'support', 'customer', 'visitor', 'agent', 'bot'],
    text: 'An AI assistant can answer questions, qualify leads, recommend services, and hand complex chats to your team.',
    actions: [
      { label: 'View Services', route: 'services.php' },
      { label: 'Book Demo', route: 'schedule-demo.php' },
    ],
  },
  {
    id: 'analytics',
    topic: 'Data Analytics',
    keywords: ['analytics', 'dashboard', 'report', 'data', 'insight', 'metrics', 'kpi'],
    text: 'Dashboards can track leads, demo requests, support topics, conversion points, and follow-up status.',
    actions: [
      { label: 'View Services', route: 'services.php' },
      { label: 'Talk to Team', handover: 'Software Assistance' },
    ],
  },
  {
    id: 'pricing',
    topic: 'Pricing Question',
    keywords: ['price', 'pricing', 'cost', 'budget', 'package', 'quote'],
    text: 'Pricing depends on scope, integrations, and launch size. The team can give you a clearer estimate after a short review.',
    actions: [
      { label: 'Request Pricing Help', handover: 'Pricing Question' },
      { label: 'Schedule Demo', route: 'schedule-demo.php' },
    ],
  },
  {
    id: 'events',
    topic: 'Events',
    keywords: ['event', 'webinar', 'workshop', 'session', 'briefing'],
    text: 'You can view upcoming AI briefings, webinars, and practical demo sessions on the events page.',
    actions: [
      { label: 'Open Events', route: 'events.php' },
      { label: 'Book Demo', route: 'schedule-demo.php' },
    ],
  },
  {
    id: 'security',
    topic: 'Software Assistance',
    keywords: ['security', 'secure', 'privacy', 'data', 'safe', 'compliance', 'risk'],
    text: 'Security is handled through validated forms, protected admin access, controlled handover, and careful customer data handling.',
    actions: [
      { label: 'Talk to Team', handover: 'Software Assistance' },
      { label: 'View Services', route: 'services.php' },
    ],
  },
  {
    id: 'handover',
    topic: 'Sales Representative',
    keywords: ['sales', 'human', 'person', 'team', 'contact', 'call me', 'follow up', 'handover'],
    text: 'I can send this conversation to the team so they can follow up with context.',
    actions: [
      { label: 'Start Handover', handover: 'Sales Representative' },
      { label: 'Schedule Demo', route: 'schedule-demo.php' },
    ],
  },
];

function getDefaultSurface() {
  return document.getElementById('chatWidget') || document.querySelector('[data-assistant-surface]');
}

function getSurface(source) {
  if (source && source.closest) {
    return source.closest('[data-assistant-surface]') || getDefaultSurface();
  }
  return getDefaultSurface();
}

function getActiveSurface() {
  const widget = document.getElementById('chatWidget');
  if (widget && widget.classList.contains('open')) {
    return widget;
  }
  return document.querySelector('.chat-panel[data-assistant-surface]') || getDefaultSurface();
}

function getBody(surface) {
  return surface ? surface.querySelector('[data-chat-body]') : document.getElementById('chatBody');
}

function getInput(surface) {
  return surface ? surface.querySelector('.chat-input textarea, .chat-input input') : document.getElementById('chatInput');
}

function getSubmit(surface) {
  return surface ? surface.querySelector('[data-assistant-submit], .chat-input button') : null;
}

function clampValue(value, min, max) {
  return Math.max(min, Math.min(value, max));
}

function positionWidgetNearLauncher() {
  const widget = document.getElementById('chatWidget');
  const launcher = document.querySelector('.chatbot-launcher');
  if (!widget || !launcher || !widget.classList.contains('open')) {
    return;
  }

  const margin = 12;
  const gap = 12;
  const launcherRect = launcher.getBoundingClientRect();
  const widgetWidth = Math.min(widget.offsetWidth || 360, window.innerWidth - margin * 2);
  const widgetHeight = Math.min(widget.offsetHeight || 520, window.innerHeight - margin * 2);
  const maxX = window.innerWidth - widgetWidth - margin;
  const maxY = window.innerHeight - widgetHeight - margin;

  const leftSpace = launcherRect.left - gap - margin;
  const rightSpace = window.innerWidth - launcherRect.right - gap - margin;
  const aboveSpace = launcherRect.top - gap - margin;
  const belowSpace = window.innerHeight - launcherRect.bottom - gap - margin;

  let x;
  let y;

  if (leftSpace >= widgetWidth || leftSpace >= rightSpace) {
    x = launcherRect.left - widgetWidth - gap;
    y = launcherRect.top + (launcherRect.height - widgetHeight) / 2;
  } else if (rightSpace >= widgetWidth) {
    x = launcherRect.right + gap;
    y = launcherRect.top + (launcherRect.height - widgetHeight) / 2;
  } else if (aboveSpace >= belowSpace) {
    x = launcherRect.left + (launcherRect.width - widgetWidth) / 2;
    y = launcherRect.top - widgetHeight - gap;
  } else {
    x = launcherRect.left + (launcherRect.width - widgetWidth) / 2;
    y = launcherRect.bottom + gap;
  }

  widget.style.left = `${clampValue(x, margin, Math.max(margin, maxX))}px`;
  widget.style.top = `${clampValue(y, margin, Math.max(margin, maxY))}px`;
  widget.style.right = 'auto';
  widget.style.bottom = 'auto';
}

function openAssistant() {
  const widget = document.getElementById('chatWidget');
  const launcher = document.querySelector('.chatbot-launcher');
  if (widget) {
    widget.classList.add('open');
    if (launcher) {
      launcher.setAttribute('aria-expanded', 'true');
      launcher.setAttribute('aria-label', 'Close AI assistant');
    }
    positionWidgetNearLauncher();
    window.requestAnimationFrame(positionWidgetNearLauncher);
    window.setTimeout(() => {
      positionWidgetNearLauncher();
      const input = getInput(widget);
      if (input) {
        input.focus();
      }
    }, 160);
  }
}

function closeAssistant() {
  const widget = document.getElementById('chatWidget');
  const launcher = document.querySelector('.chatbot-launcher');
  if (widget) {
    widget.classList.remove('open');
    if (launcher) {
      launcher.setAttribute('aria-expanded', 'false');
      launcher.setAttribute('aria-label', 'Open AI assistant');
    }
  }
}

function toggleAssistant() {
  const widget = document.getElementById('chatWidget');
  if (widget && widget.classList.contains('open')) {
    closeAssistant();
    return;
  }
  openAssistant();
}

function addHistory(surface, type, text) {
  if (!surface || !text) {
    return;
  }
  const history = assistantHistories.get(surface) || [];
  history.push({ type, text, time: new Date() });
  assistantHistories.set(surface, history.slice(-16));
}

function buildSummary(surface) {
  const history = assistantHistories.get(surface) || [];
  if (!history.length) {
    return 'Customer requested human support after opening the AI assistant.';
  }

  return history
    .slice(-10)
    .map((entry) => `${entry.type === 'user' ? 'Visitor' : 'AI-Solutions'}: ${entry.text}`)
    .join('\n');
}

function getHistoryPayload(surface) {
  const history = assistantHistories.get(surface) || [];
  return history.slice(-8).map((entry) => ({
    role: entry.type === 'user' ? 'user' : 'assistant',
    content: entry.text,
  }));
}

function rememberSummary(surface) {
  try {
    window.localStorage.setItem('aiSolutionsAssistantSummary', buildSummary(surface));
  } catch (error) {
    // Local storage can be unavailable in locked-down browsers.
  }
}

async function requestAssistantReply(message, surface) {
  if (!assistantConfig.csrfToken || !assistantConfig.endpoint) {
    return { ...getSmartReply(message), source: 'local' };
  }

  const response = await fetch(assistantConfig.endpoint, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': assistantConfig.csrfToken,
    },
    body: JSON.stringify({
      message,
      history: getHistoryPayload(surface),
    }),
  });

  const data = await response.json().catch(() => null);
  if (!response.ok || !data || !data.ok) {
    throw new Error(data?.error || 'AI-Solutions could not reach the assistant service.');
  }

  return {
    text: data.reply,
    actions: Array.isArray(data.actions) && data.actions.length ? data.actions : defaultActions,
    topic: data.topic || 'Sales Representative',
    source: data.source || 'local',
  };
}

function createActionButton(action) {
  const button = document.createElement('button');
  button.type = 'button';
  button.textContent = action.label;

  if (action.prompt) {
    button.dataset.prompt = action.prompt;
  }
  if (action.route) {
    button.dataset.route = action.route;
  }
  if (action.handover) {
    button.dataset.handover = action.handover;
  }

  return button;
}

function appendMessage(text, type, surface = getActiveSurface(), actions = [], options = {}) {
  const body = getBody(surface);
  if (!body) {
    return;
  }

  const msg = document.createElement('div');
  msg.className = type === 'user' ? 'user-message' : 'bot-message';
  if (options.tone) {
    msg.classList.add(`message-${options.tone}`);
  }

  const copy = document.createElement('span');
  copy.className = 'message-copy';
  copy.textContent = text;
  msg.appendChild(copy);

  const time = document.createElement('span');
  time.className = 'message-time';
  time.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  msg.appendChild(time);

  if (Array.isArray(actions) && actions.length) {
    const actionRow = document.createElement('div');
    actionRow.className = 'message-actions';
    actions.forEach((action) => actionRow.appendChild(createActionButton(action)));
    msg.appendChild(actionRow);
  }

  body.appendChild(msg);
  body.scrollTop = body.scrollHeight;
  addHistory(surface, type, text);
  rememberSummary(surface);
}

function showTyping(surface) {
  const body = getBody(surface);
  if (!body) {
    return null;
  }

  const typing = document.createElement('div');
  typing.className = 'bot-message typing-message';
  typing.setAttribute('aria-label', 'AI-Solutions is typing');
  typing.innerHTML = '<span></span><span></span><span></span>';
  body.appendChild(typing);
  body.scrollTop = body.scrollHeight;
  return typing;
}

function includesAny(text, words) {
  return words.some((word) => text.includes(word));
}

function getSmartReply(message) {
  const text = message.toLowerCase();

  if (includesAny(text, ['who are you', 'what are you', 'your name', 'who r u', 'what r u'])) {
    return {
      text: 'I am ai-solutions chatbot',
      actions: defaultActions,
      topic: 'Virtual Assistant',
    };
  }

  if (includesAny(text, ['hello', 'hi ', 'hey', 'good morning', 'good afternoon'])) {
    return {
      text: 'Hi. I can help with services, demos, events, or sales support.',
      actions: defaultActions,
      topic: 'Sales Representative',
    };
  }

  if (includesAny(text, ['thank', 'thanks', 'perfect', 'great'])) {
    return {
      text: 'You are welcome. Choose the next step whenever you are ready.',
      actions: defaultActions,
      topic: 'Sales Representative',
    };
  }

  const priorityMatch = ['pricing', 'demo', 'handover']
    .map((id) => knowledgeBase.find((item) => item.id === id))
    .find((item) => item && includesAny(text, item.keywords));
  if (priorityMatch) {
    return priorityMatch;
  }

  const match = knowledgeBase.find((item) => includesAny(text, item.keywords));
  if (match) {
    return match;
  }

  return {
    text: 'I can help route this to services, a demo, events, or a sales follow-up.',
    actions: defaultActions,
    topic: 'Sales Representative',
  };
}

function reply(message) {
  return getSmartReply(message).text;
}

async function handleUserMessage(text, surface) {
  appendMessage(text, 'user', surface);

  const typing = showTyping(surface);
  const submit = getSubmit(surface);
  if (submit) {
    submit.disabled = true;
    submit.textContent = 'Sending';
  }

  const started = Date.now();

  try {
    const answer = await requestAssistantReply(text, surface);
    const delay = Math.max(260, Math.min(900, 420 - (Date.now() - started)));
    window.setTimeout(() => {
      if (typing) {
        typing.remove();
      }
      appendMessage(answer.text, 'bot', surface, answer.actions || defaultActions);
    }, delay);
  } catch (error) {
    if (typing) {
      typing.remove();
    }
    const answer = getSmartReply(text);
    appendMessage(answer.text, 'bot', surface, answer.actions || defaultActions, { tone: 'fallback' });
  } finally {
    if (submit) {
      submit.disabled = false;
      submit.textContent = 'Send';
    }
  }
}

function quickAsk(text, trigger) {
  const surface = trigger ? getSurface(trigger) : getActiveSurface();
  if (!trigger && surface && surface.id === 'chatWidget') {
    openAssistant();
  }
  handleUserMessage(text, surface);
}

function sendChat(event) {
  event.preventDefault();
  const form = event.target;
  const surface = getSurface(form);
  const input = form.querySelector('textarea, input');
  if (!input) {
    return;
  }

  const msg = input.value.trim();
  if (!msg) {
    return;
  }

  input.value = '';
  input.style.height = '';
  handleUserMessage(msg, surface);
}

function renderWelcome(surface) {
  const body = getBody(surface);
  if (!body) {
    return;
  }

  appendMessage('Hi, I am AI-Solutions. Ask about services, pricing, demos, automation, or human handover.', 'bot', surface);

  const quickActions = document.createElement('div');
  quickActions.className = 'quick-actions';
  quickActions.dataset.assistantOptions = '';
  defaultActions.forEach((action) => quickActions.appendChild(createActionButton(action)));
  body.appendChild(quickActions);
  body.scrollTop = 0;
}

function resetAssistant(trigger) {
  const surface = getSurface(trigger);
  const body = getBody(surface);
  if (!body) {
    return;
  }

  assistantHistories.set(surface, []);
  body.innerHTML = '';
  renderWelcome(surface);
  const input = getInput(surface);
  if (input) {
    input.focus();
  }
}

function prepareHandover(source, topic = 'Sales Representative') {
  const surface = getSurface(source);
  const summary = buildSummary(surface);
  rememberSummary(surface);

  const url = new URL('chatbot-handover.php', window.location.href);
  url.searchParams.set('topic', topic);
  url.searchParams.set('summary', summary.slice(0, 1800));
  window.location.href = url.toString();
}

function seedHistory(surface) {
  if (!surface || assistantHistories.has(surface)) {
    return;
  }

  const entries = [];
  surface.querySelectorAll('.bot-message, .user-message').forEach((message) => {
    if (message.classList.contains('typing-message')) {
      return;
    }
    const copy = message.querySelector('.message-copy');
    const text = (copy ? copy.textContent : message.textContent).trim();
    if (text) {
      entries.push({
        type: message.classList.contains('user-message') ? 'user' : 'bot',
        text,
        time: new Date(),
      });
    }
  });
  assistantHistories.set(surface, entries.slice(-16));
}

function clampLauncherPosition(launcher, x, y) {
  const margin = 8;
  const width = launcher.offsetWidth || 92;
  const height = launcher.offsetHeight || 92;
  return {
    x: Math.max(margin, Math.min(x, window.innerWidth - width - margin)),
    y: Math.max(margin, Math.min(y, window.innerHeight - height - margin)),
  };
}

function setLauncherPosition(launcher, x, y, save = false) {
  const position = clampLauncherPosition(launcher, x, y);
  launcher.style.left = `${position.x}px`;
  launcher.style.top = `${position.y}px`;
  launcher.style.right = 'auto';
  launcher.style.bottom = 'auto';
  positionWidgetNearLauncher();

  if (save) {
    try {
      window.localStorage.setItem('aiSolutionsBotPosition', JSON.stringify(position));
    } catch (error) {
      // The launcher still works when storage is blocked.
    }
  }
}

function restoreLauncherPosition(launcher) {
  try {
    window.localStorage.removeItem('aiSolutionsBotPosition');
    launcher.style.left = '';
    launcher.style.top = '';
    launcher.style.right = '';
    launcher.style.bottom = '';
  } catch (error) {
    // Ignore malformed or unavailable storage.
  }
}

function setupLauncher() {
  const launcher = document.querySelector('.chatbot-launcher');
  if (!launcher) {
    return;
  }

  // The launcher is anchored to the bottom-right corner via CSS. Clear any
  // position saved by older builds so it never drifts, and keep it fixed.
  restoreLauncherPosition(launcher);

  launcher.addEventListener('click', () => {
    toggleAssistant();
  });

  // Keep the open chat panel aligned to the launcher when the window resizes.
  window.addEventListener('resize', () => {
    positionWidgetNearLauncher();
  });
}

function watchFeedbackSection() {
  const section = document.getElementById('visitor-feedback');
  if (!section || !('IntersectionObserver' in window)) {
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    const isVisible = entries.some((entry) => entry.isIntersecting);
    document.body.classList.toggle('feedback-in-view', isVisible);
    positionWidgetNearLauncher();
  }, { threshold: 0.12 });

  observer.observe(section);
}

function resizeAssistantInput(input) {
  if (!input || input.tagName !== 'TEXTAREA') {
    return;
  }
  input.style.height = 'auto';
  input.style.height = `${Math.min(input.scrollHeight, 118)}px`;
}

function setupAssistantInputs() {
  document.querySelectorAll('.chat-input textarea').forEach((input) => {
    resizeAssistantInput(input);
    input.addEventListener('input', () => resizeAssistantInput(input));
    input.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' && !event.shiftKey && window.matchMedia('(min-width: 760px)').matches) {
        event.preventDefault();
        input.closest('form')?.requestSubmit();
      }
    });
  });
}

document.addEventListener('click', (event) => {
  const promptButton = event.target.closest('[data-prompt]');
  if (promptButton) {
    event.preventDefault();
    quickAsk(promptButton.dataset.prompt, promptButton);
    return;
  }

  const routeButton = event.target.closest('[data-route]');
  if (routeButton) {
    event.preventDefault();
    window.location.href = routeButton.dataset.route;
    return;
  }

  const handoverControl = event.target.closest('[data-handover], [data-handover-link]');
  if (handoverControl) {
    event.preventDefault();
    prepareHandover(handoverControl, handoverControl.dataset.handover || 'Sales Representative');
  }
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-assistant-surface]').forEach(seedHistory);
  setupAssistantInputs();
  setupLauncher();
  watchFeedbackSection();

  const params = new URLSearchParams(window.location.search);
  const starterPrompt = params.get('ask');
  if (params.get('assistant') === 'open') {
    openAssistant();
  }
  if (starterPrompt) {
    window.setTimeout(() => quickAsk(starterPrompt), 220);
  }
});
