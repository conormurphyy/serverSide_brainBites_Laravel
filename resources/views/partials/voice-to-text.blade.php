<script>
    (function () {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            return;
        }

        const selector = [
            'textarea',
            'input[type="text"]',
            'input[type="search"]',
            'input[type="url"]',
            'input[type="tel"]',
        ].join(',');

        const activeRecognition = new WeakMap();

        function insertAtCursor(field, text) {
            const insertion = text || '';
            const start = typeof field.selectionStart === 'number' ? field.selectionStart : field.value.length;
            const end = typeof field.selectionEnd === 'number' ? field.selectionEnd : field.value.length;
            const currentValue = field.value || '';

            field.value = currentValue.slice(0, start) + insertion + currentValue.slice(end);
            const cursor = start + insertion.length;

            if (typeof field.setSelectionRange === 'function') {
                field.setSelectionRange(cursor, cursor);
            }

            field.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function resetButton(button) {
            button.innerHTML = button.dataset.iconMic || '';
            button.setAttribute('aria-label', 'Start voice to text');
            button.setAttribute('aria-pressed', 'false');
            button.classList.remove('bb-vtt-button--listening');
        }

        function setListening(button) {
            button.innerHTML = button.dataset.iconStop || '';
            button.setAttribute('aria-label', 'Stop voice to text');
            button.setAttribute('aria-pressed', 'true');
            button.classList.add('bb-vtt-button--listening');
        }

        function shouldAttachVoice(field) {
            if (!(field instanceof HTMLElement)) {
                return false;
            }

            if (field.dataset.bbVoice === 'off') {
                return false;
            }

            if (field instanceof HTMLInputElement) {
                const type = (field.type || '').toLowerCase();
                const disallowedTypes = new Set([
                    'email',
                    'password',
                    'hidden',
                    'number',
                    'date',
                    'datetime-local',
                    'month',
                    'week',
                    'time',
                    'color',
                    'file',
                ]);

                if (disallowedTypes.has(type)) {
                    return false;
                }

                const autocomplete = String(field.getAttribute('autocomplete') || '').toLowerCase();
                if (/(username|email|current-password|new-password|one-time-code)/.test(autocomplete)) {
                    return false;
                }

                const identityText = `${field.name || ''} ${field.id || ''} ${field.placeholder || ''}`.toLowerCase();
                if (/(email|username|user name|login|password|passcode|otp|one-time|verification|token|pin)/.test(identityText)) {
                    return false;
                }
            }

            return true;
        }

        function attachButton(field) {
            if (!(field instanceof HTMLElement)) {
                return;
            }

            if (field.dataset.bbVoiceAttached === 'true' || field.disabled || field.readOnly || !shouldAttachVoice(field)) {
                return;
            }

            field.dataset.bbVoiceAttached = 'true';

            const wrapper = document.createElement('div');
            wrapper.className = 'bb-vtt-wrap';
            field.parentNode?.insertBefore(wrapper, field);
            wrapper.appendChild(field);
            field.classList.add('bb-vtt-field');

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'bb-vtt-button';
            button.setAttribute('aria-label', 'Start voice to text');
            button.setAttribute('aria-pressed', 'false');
            button.title = 'Voice to text';
            button.dataset.iconMic = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="bb-vtt-icon"><path d="M12 15a3 3 0 0 0 3-3V7a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3zm5-3a1 1 0 1 0-2 0 3 3 0 0 1-6 0 1 1 0 1 0-2 0 5 5 0 0 0 4 4.9V20H9a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2h-2v-3.1A5 5 0 0 0 17 12z"/></svg>';
            button.dataset.iconStop = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" class="bb-vtt-icon"><path d="M7 7h10v10H7z"/></svg>';
            button.innerHTML = button.dataset.iconMic;

            wrapper.appendChild(button);

            button.addEventListener('click', () => {
                const current = activeRecognition.get(field);

                if (current) {
                    current.stop();
                    activeRecognition.delete(field);
                    resetButton(button);
                    return;
                }

                const recognition = new SpeechRecognition();
                recognition.lang = document.documentElement.lang || 'en-US';
                recognition.continuous = true;
                recognition.interimResults = true;

                let finalBuffer = '';
                let lastFinalValue = field.value || '';

                recognition.onstart = () => {
                    lastFinalValue = field.value || '';
                    setListening(button);
                };

                recognition.onresult = (event) => {
                    let interimText = '';

                    for (let i = event.resultIndex; i < event.results.length; i += 1) {
                        const transcript = event.results[i][0]?.transcript || '';
                        if (event.results[i].isFinal) {
                            finalBuffer += transcript + ' ';
                        } else {
                            interimText += transcript;
                        }
                    }

                    const combined = (finalBuffer + interimText).trim();
                    field.value = lastFinalValue + (combined ? ` ${combined}` : '');
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                };

                recognition.onerror = () => {
                    recognition.stop();
                };

                recognition.onend = () => {
                    const finalText = finalBuffer.trim();
                    field.value = lastFinalValue;
                    if (finalText) {
                        insertAtCursor(field, ` ${finalText}`);
                    }
                    activeRecognition.delete(field);
                    resetButton(button);
                };

                activeRecognition.set(field, recognition);
                recognition.start();
            });
        }

        function scanFields(root) {
            const scope = root instanceof HTMLElement || root instanceof Document ? root : document;
            scope.querySelectorAll(selector).forEach(attachButton);
        }

        document.addEventListener('DOMContentLoaded', () => {
            scanFields(document);

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (!(node instanceof HTMLElement)) {
                            return;
                        }

                        if (node.matches(selector)) {
                            attachButton(node);
                        }

                        scanFields(node);
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true,
            });
        });
    })();
</script>

<style>
    .bb-vtt-wrap {
        position: relative;
        width: 100%;
    }

    .bb-vtt-wrap .bb-vtt-field {
        padding-right: 3rem !important;
    }

    .bb-vtt-button {
        position: absolute;
        top: 50%;
        right: 0.45rem;
        transform: translateY(-50%);
        z-index: 2;
        border: 1px solid rgba(14, 116, 144, 0.35);
        border-radius: 9999px;
        width: 2rem;
        height: 2rem;
        padding: 0;
        color: #0f172a;
        background: #f0fdfa;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .bb-vtt-button:hover {
        background: #cffafe;
    }

    .bb-vtt-button--listening {
        background: #fef2f2;
        border-color: rgba(220, 38, 38, 0.5);
        color: #991b1b;
    }

    .bb-vtt-icon {
        width: 1rem;
        height: 1rem;
        fill: currentColor;
    }

    .bb-vtt-wrap textarea + .bb-vtt-button {
        top: 0.5rem;
        transform: none;
    }
</style>
