@if (auth()->check() && auth()->user()->isAdmin())
<style>
    .assistant-launcher {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 80;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 48px;
        padding: 0 17px;
        border: 0;
        border-radius: 24px;
        background: #245b38;
        color: #fff;
        box-shadow: 0 8px 26px rgba(20, 52, 33, .24);
        font: 700 14px/1 Arial, Helvetica, sans-serif;
        cursor: pointer;
    }

    .assistant-launcher:hover,
    .assistant-launcher:focus-visible {
        background: #193f29;
    }

    .assistant-launcher-icon {
        display: grid;
        width: 25px;
        height: 25px;
        place-items: center;
        border-radius: 50%;
        background: #d7edcf;
        color: #193f29;
        font-size: 15px;
    }

    .assistant-window {
        position: fixed;
        right: 22px;
        bottom: 82px;
        z-index: 81;
        display: flex;
        flex-direction: column;
        width: min(390px, calc(100vw - 28px));
        height: min(590px, calc(100dvh - 112px));
        min-height: 360px;
        overflow: hidden;
        border: 1px solid #dfe6dc;
        border-radius: 10px;
        background: #fff;
        color: #263b2b;
        box-shadow: 0 16px 48px rgba(20, 40, 25, .24);
        font: 14px/1.45 Arial, Helvetica, sans-serif;
    }

    .assistant-window[hidden] {
        display: none;
    }

    .assistant-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 66px;
        padding: 12px 15px;
        background: #245b38;
        color: #fff;
    }

    .assistant-title {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .assistant-subtitle {
        margin: 3px 0 0;
        color: #e4efe5;
        font-size: 12px;
    }

    .assistant-close {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        place-items: center;
        border: 1px solid rgba(255,255,255,.35);
        border-radius: 5px;
        background: transparent;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
    }

    .assistant-messages {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 12px;
        overflow-y: auto;
        padding: 16px 13px;
        background: #f5f8f3;
        overscroll-behavior: contain;
    }

    .assistant-message {
        max-width: 90%;
        padding: 10px 12px;
        border: 1px solid #e0e7dc;
        border-radius: 8px 8px 8px 2px;
        background: #fff;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .assistant-message.user {
        align-self: flex-end;
        border-color: #245b38;
        border-radius: 8px 8px 2px 8px;
        background: #245b38;
        color: #fff;
    }

    .assistant-quick-replies {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .assistant-quick-reply {
        padding: 7px 10px;
        border: 1px solid #9db59f;
        border-radius: 16px;
        background: #fff;
        color: #245b38;
        font-size: 12px;
        cursor: pointer;
    }

    .assistant-products {
        display: grid;
        gap: 8px;
        width: 100%;
    }

    .assistant-product {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        gap: 10px;
        padding: 9px;
        border: 1px solid #e0e7dc;
        border-radius: 6px;
        background: #fff;
        color: #263b2b;
        text-decoration: none;
    }

    .assistant-product img,
    .assistant-product-placeholder {
        display: grid;
        width: 52px;
        height: 52px;
        place-items: center;
        border-radius: 4px;
        background: #edf3eb;
        color: #657267;
        object-fit: cover;
        font-size: 11px;
    }

    .assistant-product-copy {
        min-width: 0;
        align-self: center;
    }

    .assistant-product-copy strong,
    .assistant-product-copy span {
        display: block;
        overflow-wrap: anywhere;
    }

    .assistant-product-copy span {
        margin-top: 2px;
        color: #657267;
        font-size: 12px;
    }

    .assistant-form {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 11px;
        border-top: 1px solid #dfe6dc;
        background: #fff;
    }

    .assistant-input {
        width: 100%;
        min-width: 0;
        min-height: 42px;
        padding: 9px 11px;
        border: 1px solid #cbd8cb;
        border-radius: 6px;
        background: #fff;
        color: #263b2b;
        font: inherit;
    }

    .assistant-send {
        min-height: 42px;
        padding: 0 14px;
        border: 0;
        border-radius: 6px;
        background: #245b38;
        color: #fff;
        font-weight: 700;
        cursor: pointer;
    }

    .assistant-send:disabled,
    .assistant-input:disabled {
        opacity: .65;
        cursor: wait;
    }

    @media (max-width: 480px) {
        .assistant-launcher {
            right: 14px;
            bottom: 14px;
        }

        .assistant-window {
            right: 10px;
            bottom: 72px;
            width: min(390px, calc(100vw - 48px));
            height: min(620px, calc(100dvh - 90px));
            max-height: 620px;
        }
    }
</style>

<div class="assistant-widget" data-assistant-widget>
    <section
        class="assistant-window"
        id="storeAssistantWindow"
        role="dialog"
        aria-labelledby="storeAssistantTitle"
        aria-modal="false"
        hidden
    >
        <header class="assistant-header">
            <div>
                <h2 class="assistant-title" id="storeAssistantTitle">H&amp;K Store Assistant</h2>
                <p class="assistant-subtitle">Product help, orders, and store FAQs</p>
            </div>
            <button class="assistant-close" type="button" aria-label="Close assistant" data-assistant-close>&times;</button>
        </header>

        <div class="assistant-messages" role="log" aria-live="polite" aria-relevant="additions text" data-assistant-messages>
            <div class="assistant-message">Hi! I can help find available products, explain payment and delivery, or check your recent order status.</div>
            <div class="assistant-quick-replies" data-assistant-quick-replies>
                <button class="assistant-quick-reply" type="button">Recommend a product</button>
                <button class="assistant-quick-reply" type="button">Payment options</button>
                <button class="assistant-quick-reply" type="button">Delivery and pickup</button>
                <button class="assistant-quick-reply" type="button">Track my order</button>
            </div>
        </div>

        <form class="assistant-form" data-assistant-form>
            <input
                class="assistant-input"
                type="text"
                name="message"
                maxlength="300"
                placeholder="Ask a question..."
                aria-label="Ask the store assistant a question"
                autocomplete="off"
                required
            >
            <button class="assistant-send" type="submit">Send</button>
        </form>
    </section>

    <button
        class="assistant-launcher"
        type="button"
        aria-expanded="false"
        aria-controls="storeAssistantWindow"
        data-assistant-open
    >
        <span class="assistant-launcher-icon" aria-hidden="true">?</span>
        <span>Ask assistant</span>
    </button>
</div>

<script>
    (() => {
        const widget = document.querySelector('[data-assistant-widget]');
        if (!widget) return;

        const windowPanel = widget.querySelector('.assistant-window');
        const openButton = widget.querySelector('[data-assistant-open]');
        const closeButton = widget.querySelector('[data-assistant-close]');
        const messages = widget.querySelector('[data-assistant-messages]');
        const form = widget.querySelector('[data-assistant-form]');
        const input = form.querySelector('input[name="message"]');
        const sendButton = form.querySelector('button[type="submit"]');
        const quickReplies = widget.querySelector('[data-assistant-quick-replies]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const appendMessage = (text, sender = 'assistant') => {
            const message = document.createElement('div');
            message.className = `assistant-message${sender === 'user' ? ' user' : ''}`;
            message.textContent = text;
            messages.append(message);
            messages.scrollTop = messages.scrollHeight;
            return message;
        };

        const renderReply = (reply) => {
            appendMessage(reply.message || 'Sorry, I could not prepare a reply.');

            if (Array.isArray(reply.products) && reply.products.length) {
                const list = document.createElement('div');
                list.className = 'assistant-products';

                reply.products.forEach((product) => {
                    const link = document.createElement('a');
                    link.className = 'assistant-product';
                    link.href = product.url;

                    if (product.image) {
                        const image = document.createElement('img');
                        image.src = product.image;
                        image.alt = '';
                        image.loading = 'lazy';
                        link.append(image);
                    } else {
                        const placeholder = document.createElement('span');
                        placeholder.className = 'assistant-product-placeholder';
                        placeholder.textContent = 'Product';
                        link.append(placeholder);
                    }

                    const copy = document.createElement('span');
                    copy.className = 'assistant-product-copy';
                    const name = document.createElement('strong');
                    name.textContent = product.name;
                    const details = document.createElement('span');
                    details.textContent = `${product.category} | ${product.stock} | ${product.price}`;
                    copy.append(name, details);
                    link.append(copy);
                    list.append(link);
                });

                messages.append(list);
            }

            if (Array.isArray(reply.quick_replies) && reply.quick_replies.length) {
                const list = document.createElement('div');
                list.className = 'assistant-quick-replies';
                reply.quick_replies.forEach((text) => {
                    const button = document.createElement('button');
                    button.className = 'assistant-quick-reply';
                    button.type = 'button';
                    button.textContent = text;
                    list.append(button);
                });
                messages.append(list);
            }

            messages.scrollTop = messages.scrollHeight;
        };

        const sendMessage = async (text) => {
            const message = text.trim();
            if (!message || sendButton.disabled) return;

            appendMessage(message, 'user');
            quickReplies?.remove();
            input.value = '';
            input.disabled = true;
            sendButton.disabled = true;
            sendButton.textContent = '...';
            const pending = appendMessage('Checking store information...');

            try {
                const response = await fetch('{{ route('assistant.message') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                    },
                    body: JSON.stringify({ message }),
                });

                const reply = await response.json();
                pending.remove();

                if (!response.ok) {
                    appendMessage(reply.message || 'I could not process that question. Please try again.');
                } else {
                    renderReply(reply);
                }
            } catch {
                pending.remove();
                appendMessage('The assistant is temporarily unavailable. Please try again in a moment.');
            } finally {
                input.disabled = false;
                sendButton.disabled = false;
                sendButton.textContent = 'Send';
                input.focus();
            }
        };

        openButton.addEventListener('click', () => {
            windowPanel.hidden = false;
            openButton.setAttribute('aria-expanded', 'true');
            input.focus();
        });

        closeButton.addEventListener('click', () => {
            windowPanel.hidden = true;
            openButton.setAttribute('aria-expanded', 'false');
            openButton.focus();
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            sendMessage(input.value);
        });

        widget.addEventListener('click', (event) => {
            const button = event.target.closest('.assistant-quick-reply');
            if (button) sendMessage(button.textContent);
        });
    })();
</script>
@endif
