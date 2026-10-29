@auth
    @php($unreadNotificationCount = auth()->user()->unreadNotifications()->count())
    <style>
        .notification-center {
            position: relative;
            display: inline-flex;
            flex-shrink: 0;
        }

        .notification-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .notification-toggle {
            position: relative;
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            color: #fff;
            font-size: 23px;
            cursor: pointer;
        }

        .notification-toggle:hover,
        .notification-toggle:focus-visible {
            background: rgba(255, 255, 255, .2);
        }

        .notification-count {
            position: absolute;
            top: -4px;
            right: -5px;
            min-width: 18px;
            padding: 2px 5px;
            border: 2px solid #245333;
            border-radius: 20px;
            background: #d93025;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            text-align: center;
        }

        .notification-count[hidden] {
            display: none;
        }

        .notification-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 100;
            width: min(360px, calc(100vw - 24px));
            max-height: min(440px, 70vh);
            overflow: auto;
            border: 1px solid #dfe6dc;
            border-radius: 12px;
            background: #fff;
            color: #263b2b;
            box-shadow: 0 12px 34px rgba(0, 0, 0, .22);
        }

        .notification-panel[hidden] {
            display: none;
        }

        .notification-panel-header {
            position: sticky;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 15px;
            border-bottom: 1px solid #e8eee8;
            background: #fff;
        }

        .notification-mark-all {
            padding: 4px 0;
            border: 0;
            background: transparent;
            color: #245b38;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .notification-item {
            display: block;
            width: 100%;
            padding: 12px 15px;
            border: 0;
            border-bottom: 1px solid #edf1ed;
            background: #fff;
            color: #263b2b;
            text-align: left;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f3f8f3;
        }

        .notification-item strong,
        .notification-item span,
        .notification-item time {
            display: block;
        }

        .notification-item strong {
            margin-bottom: 4px;
            font-size: 13px;
        }

        .notification-item span {
            color: #59685d;
            font-size: 12px;
            line-height: 1.4;
        }

        .notification-item time {
            margin-top: 6px;
            color: #839087;
            font-size: 10px;
        }

        .notification-empty {
            padding: 20px 15px;
            margin: 0;
            color: #718078;
            font-size: 12px;
            text-align: center;
        }

        :root[data-theme="dark"] .notification-panel {
            border-color: #293149;
            background: #131b2e;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .notification-panel-header {
            border-color: #293149;
            background: #131b2e;
        }

        :root[data-theme="dark"] .notification-mark-all {
            color: #9fe0b0;
        }

        :root[data-theme="dark"] .notification-item {
            border-color: #293149;
            background: #131b2e;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .notification-item:hover {
            background: #1a2439;
        }

        :root[data-theme="dark"] .notification-item span,
        :root[data-theme="dark"] .notification-item time,
        :root[data-theme="dark"] .notification-empty {
            color: #aab6cb;
        }

        @media (max-width: 560px) {
            .notification-panel {
                right: -50px;
            }

            .notification-floating {
                top: 12px;
                right: 12px;
            }
        }
    </style>

    <div class="notification-center" data-notification-center
         data-user-id="{{ auth()->id() }}"
         data-index-url="{{ route('notifications.index') }}"
         data-read-all-url="{{ route('notifications.read-all') }}"
         data-read-url-template="{{ url('/notifications/__notification__/read') }}">
        <button class="notification-toggle" type="button"
                aria-label="Notifications" aria-expanded="false"
                aria-controls="notification-panel">
            <span aria-hidden="true">&#128276;</span>
            <span class="notification-count" data-notification-count
                  @if ($unreadNotificationCount === 0) hidden @endif>
                {{ $unreadNotificationCount }}
            </span>
        </button>
        <section class="notification-panel" id="notification-panel" hidden>
            <div class="notification-panel-header">
                <strong>Notifications</strong>
                <button type="button" class="notification-mark-all"
                        data-notification-mark-all>Mark all read</button>
            </div>
            <div class="notification-list" data-notification-list>
                <p class="notification-empty">Loading notifications…</p>
            </div>
        </section>
    </div>
@endauth
