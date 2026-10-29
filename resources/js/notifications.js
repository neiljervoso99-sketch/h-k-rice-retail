function initializeNotificationCenter(center) {
    const toggle = center.querySelector('.notification-toggle');
    const panel = center.querySelector('.notification-panel');
    const count = center.querySelector('[data-notification-count]');
    const list = center.querySelector('[data-notification-list]');
    const markAll = center.querySelector('[data-notification-mark-all]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!toggle || !panel || !count || !list || !markAll || !csrfToken) {
        console.error('Notification center could not initialize: required markup or CSRF token is missing.');
        return;
    }

    const readUrl = center.dataset.readUrlTemplate;

    function updateCount(value) {
        const unreadCount = Number(value) || 0;
        count.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        count.hidden = unreadCount === 0;
    }

    function renderNotifications(notifications) {
        list.replaceChildren();

        if (!notifications.length) {
            const empty = document.createElement('p');
            empty.className = 'notification-empty';
            empty.textContent = 'You are all caught up.';
            list.append(empty);
            return;
        }

        notifications.forEach(notification => {
            const item = document.createElement('button');
            item.className = 'notification-item';
            item.type = 'button';

            const title = document.createElement('strong');
            title.textContent = notification.title || 'Notification';

            const message = document.createElement('span');
            message.textContent = notification.message || '';

            const time = document.createElement('time');
            time.dateTime = notification.created_at || '';
            time.textContent = notification.created_at
                ? new Date(notification.created_at).toLocaleString()
                : '';

            item.append(title, message, time);
            item.addEventListener('click', () => markRead(notification));
            list.append(item);
        });
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
        });

        if (!response.ok) {
            throw new Error(`Notification request failed with status ${response.status}.`);
        }

        return response.json();
    }

    async function refresh() {
        try {
            const data = await request(center.dataset.indexUrl);
            updateCount(data.unread_count);
            renderNotifications(data.notifications);
        } catch (error) {
            console.error('Unable to load notifications.', error);
            list.replaceChildren();
            const message = document.createElement('p');
            message.className = 'notification-empty';
            message.textContent = 'Notifications could not be loaded.';
            list.append(message);
        }
    }

    async function markRead(notification) {
        try {
            const url = readUrl.replace('__notification__', encodeURIComponent(notification.id));
            const data = await request(url, { method: 'POST' });
            updateCount(data.unread_count);

            if (notification.url) {
                window.location.assign(notification.url);
                return;
            }

            await refresh();
        } catch (error) {
            console.error('Unable to mark notification as read.', error);
        }
    }

    toggle.addEventListener('click', async () => {
        const opening = panel.hidden;
        panel.hidden = !opening;
        toggle.setAttribute('aria-expanded', String(opening));

        if (opening) {
            await refresh();
        }
    });

    markAll.addEventListener('click', async () => {
        try {
            const data = await request(center.dataset.readAllUrl, { method: 'POST' });
            updateCount(data.unread_count);
            await refresh();
        } catch (error) {
            console.error('Unable to mark notifications as read.', error);
        }
    });

    document.addEventListener('click', event => {
        if (!center.contains(event.target)) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        }
    });

    if (window.Echo && center.dataset.userId) {
        window.Echo.private(`App.Models.User.${center.dataset.userId}`)
            .listen('.user.alert.created', notification => {
                updateCount(Number(count.textContent.replace('+', '')) + 1);
                if (!panel.hidden) {
                    refresh();
                }
            });
    } else {
        console.error('Notification real-time connection is unavailable.');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-notification-center]')
        .forEach(initializeNotificationCenter);
});
