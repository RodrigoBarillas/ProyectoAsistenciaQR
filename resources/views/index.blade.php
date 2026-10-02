<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>asistencia-qr — status</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0d1117;
            --terminal: #161b22;
            --border:   #30363d;
            --green:    #3fb950;
            --green-hi: #56d364;
            --red:      #f85149;
            --yellow:   #d29922;
            --blue:     #58a6ff;
            --white:    #e6edf3;
            --muted:    #6e7681;
            --dim:      #484f58;
        }

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            background: var(--bg);
            font-family: 'JetBrains Mono', 'Cascadia Code', 'Fira Mono', monospace;
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--white);
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Window ─────────────────────────────── */
        .window {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            background: var(--terminal);
        }

        /* ── Title bar ──────────────────────────── */
        .titlebar {
            display: flex;
            align-items: center;
            padding: .8rem 1.25rem;
            background: #1c2128;
            border-bottom: 1px solid var(--border);
            gap: 1rem;
            user-select: none;
        }

        .dots { display: flex; gap: .45rem; }
        .dot  { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red    { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green  { background: #28c840; }

        .titlebar-label {
            flex: 1;
            text-align: center;
            font-size: .72rem;
            color: var(--muted);
            letter-spacing: .02em;
        }

        .titlebar-right {
            width: 52px; /* balance */
            display: flex;
            justify-content: flex-end;
        }

        #refresh-btn {
            background: none;
            border: 1px solid var(--border);
            color: var(--muted);
            font-family: inherit;
            font-size: .65rem;
            padding: .15rem .5rem;
            border-radius: .3rem;
            cursor: pointer;
            letter-spacing: .03em;
            transition: color .15s, border-color .15s;
        }

        #refresh-btn:hover { color: var(--green); border-color: var(--green); }
        #refresh-btn.spinning { animation: none; }
        #refresh-btn.spinning::after { content: '…'; }

        /* ── Shell body ─────────────────────────── */
        .shell {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 1.5rem 1.75rem 2rem;
        }

        /* Custom scrollbar */
        .shell::-webkit-scrollbar { width: 6px; }
        .shell::-webkit-scrollbar-track { background: transparent; }
        .shell::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        .shell::-webkit-scrollbar-thumb:hover { background: var(--dim); }


        /* ── Prompt line ────────────────────────── */
        .prompt-line {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1.5rem;
            color: var(--muted);
        }

        .prompt-user { color: var(--green); font-weight: 700; }
        .prompt-sep  { color: var(--dim); }
        .prompt-dir  { color: var(--blue); }
        .prompt-sym  { color: var(--green-hi); }
        .prompt-cmd  { color: var(--white); }

        .cursor {
            display: inline-block;
            width: 8px;
            height: 1.1em;
            background: var(--green-hi);
            vertical-align: text-bottom;
            animation: blink .9s step-end infinite;
        }

        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

        /* ── Output ─────────────────────────────── */
        #output { display: flex; flex-direction: column; gap: 0; }

        .out-line {
            display: block;
            opacity: 0;
            animation: fadein .12s forwards;
        }

        @keyframes fadein { to { opacity: 1; } }

        /* line types */
        .ln-blank   { height: .9rem; }

        .ln-header  { color: var(--muted); font-size: .72rem; letter-spacing: .08em; }
        .ln-header span { color: var(--dim); }

        .ln-service { display: flex; align-items: baseline; gap: .6rem; }

        .svc-icon   { font-size: .95rem; flex-shrink: 0; }
        .svc-name   { font-weight: 700; }
        .svc-name.ok    { color: var(--green-hi); }
        .svc-name.error { color: var(--red); }

        .svc-tag {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            padding: .05rem .4rem;
            border-radius: .2rem;
        }

        .svc-tag.ok    { background: rgba(63,185,80,.15); color: var(--green); }
        .svc-tag.error { background: rgba(248,81,73,.15);  color: var(--red); }

        .ln-kv {
            display: flex;
            gap: 0;
            padding-left: 2rem;
        }

        .kv-key   { color: var(--muted); }
        .kv-eq    { color: var(--dim); }
        .kv-val   { color: var(--white); }
        .kv-val.ok    { color: var(--green); }
        .kv-val.error { color: var(--red); }
        .kv-val.warn  { color: var(--yellow); }

        .ln-summary {
            margin-top: .4rem;
            color: var(--muted);
            font-size: .78rem;
        }

        .ln-summary .ok-count    { color: var(--green); font-weight: 700; }
        .ln-summary .error-count { color: var(--red);   font-weight: 700; }

        .ln-error-msg {
            padding-left: 2rem;
            color: var(--red);
            font-size: .82rem;
        }

        /* ── Skeleton ───────────────────────────── */
        .skel {
            display: inline-block;
            height: .85em;
            border-radius: .15rem;
            background: linear-gradient(90deg, #21262d 25%, #2d333b 50%, #21262d 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            vertical-align: middle;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ── Responsive ─────────────────────────── */
        @media (max-width: 500px) {
            body { padding: 1rem .5rem 3rem; }
            .shell { padding: 1.25rem 1rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="window">

    <!-- Title bar -->
    <div class="titlebar">
        <div class="dots">
            <div class="dot dot-red"></div>
            <div class="dot dot-yellow"></div>
            <div class="dot dot-green"></div>
        </div>
        <span class="titlebar-label">asistencia-qr — status</span>
        <div class="titlebar-right">
            <button id="refresh-btn" onclick="loadHealth()">run</button>
        </div>
    </div>

    <!-- Shell -->
    <div class="shell">

        <div class="prompt-line">
            <span class="prompt-user">user</span>
            <span class="prompt-sep">@</span>
            <span class="prompt-dir">asistencia-qr</span>
            <span class="prompt-sym">$</span>
            <span class="prompt-cmd">./healthcheck --verbose</span>
            <span class="cursor" id="cursor"></span>
        </div>

        <div id="output"></div>

    </div>
</div>

<script>
const SERVICES = [
    {
        key:  'app',
        name: 'application',
        rows: d => [
            { k: 'name',        v: d.name },
            { k: 'environment', v: d.environment, cls: d.environment === 'production' ? '' : 'warn' },
            { k: 'version',     v: `laravel ${d.laravel} / php ${d.php_version}` },
            { k: 'debug',       v: d.debug ? 'true' : 'false', cls: d.debug ? 'warn' : 'ok' },
        ],
    },
    {
        key:  'database',
        name: 'database',
        rows: d => [
            { k: 'driver',  v: d.driver },
            { k: 'latency', v: `${d.latency_ms} ms`, cls: d.latency_ms > 150 ? 'warn' : 'ok' },
        ],
    },
    {
        key:  'cache',
        name: 'cache',
        rows: d => [
            { k: 'driver',  v: d.driver },
            { k: 'latency', v: `${d.latency_ms} ms`, cls: d.latency_ms > 150 ? 'warn' : 'ok' },
        ],
    },
    {
        key:  'queue',
        name: 'queue',
        rows: d => [
            { k: 'driver',  v: d.driver },
            { k: 'pending', v: String(d.queue_size) },
        ],
    },
    {
        key:  'storage',
        name: 'storage',
        rows: d => [
            { k: 'writable', v: d.writable ? 'true' : 'false', cls: d.writable ? 'ok' : 'error' },
        ],
    },
    {
        key:  'docs',
        name: 'api docs (swagger)',
        rows: d => [
            { k: 'url',  v: d.url },
            { k: 'http', v: d.http_code ? String(d.http_code) : '—', cls: d.http_code === 200 ? 'ok' : 'error' },
            { k: 'latency', v: d.latency_ms ? `${d.latency_ms} ms` : '—', cls: (d.latency_ms && d.latency_ms > 200) ? 'warn' : 'ok' },
        ],
    },
];

// ── Helpers ───────────────────────────────────────────
function line(cls, html, delay) {
    return { cls, html, delay };
}

function appendLines(lines) {
    const out = document.getElementById('output');
    lines.forEach(({ cls, html, delay }, i) => {
        const el = document.createElement('span');
        el.className = `out-line ${cls}`;
        el.innerHTML = html;
        el.style.animationDelay = `${delay}ms`;
        out.appendChild(el);
    });
}

function skeletonOutput() {
    const out = document.getElementById('output');
    out.innerHTML = '';
    SERVICES.forEach((svc, i) => {
        const base = i * 120;
        const svcEl = document.createElement('span');
        svcEl.className = 'out-line ln-service';
        svcEl.style.animationDelay = `${base}ms`;
        svcEl.innerHTML = `<span class="skel" style="width:12px"></span>&nbsp;<span class="skel" style="width:${60 + i * 15}px"></span>`;
        out.appendChild(svcEl);

        [1, 2].forEach((_, j) => {
            const kvEl = document.createElement('span');
            kvEl.className = 'out-line ln-kv';
            kvEl.style.animationDelay = `${base + 40 + j * 30}ms`;
            kvEl.innerHTML = `<span class="skel" style="width:${50 + j * 20}px"></span>&nbsp;<span class="skel" style="width:${40 + j * 10}px"></span>`;
            out.appendChild(kvEl);
        });

        const blankEl = document.createElement('span');
        blankEl.className = 'out-line ln-blank';
        blankEl.style.animationDelay = `${base + 80}ms`;
        out.appendChild(blankEl);
    });
}

async function loadHealth() {
    const btn    = document.getElementById('refresh-btn');
    const cursor = document.getElementById('cursor');

    btn.classList.add('spinning');
    btn.disabled = true;
    cursor.style.display = 'inline-block';

    skeletonOutput();

    try {
        const res  = await fetch('/healthcheck');
        const data = await res.json();
        const out  = document.getElementById('output');
        out.innerHTML = '';

        const ts = new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        let delay = 0;
        const step = 80;

        // header
        appendLines([
            line('out-line ln-header', `<span>#</span> running healthcheck at ${ts}`, delay),
        ]);
        delay += step;

        appendLines([line('out-line ln-blank', '', delay)]);
        delay += step / 2;

        let okCount = 0, errCount = 0;

        SERVICES.forEach(svc => {
            const d   = data[svc.key] ?? { status: 'error', error: 'no data' };
            const ok  = d.status === 'ok';
            ok ? okCount++ : errCount++;

            const icon = ok ? '✓' : '✗';
            const cls  = ok ? 'ok' : 'error';
            const tag  = ok ? 'OK' : 'FAIL';

            // service line
            appendLines([
                line('out-line ln-service', `
                    <span class="svc-icon ${cls}">${icon}</span>
                    <span class="svc-name ${cls}">${svc.name}</span>
                    <span class="svc-tag ${cls}">${tag}</span>
                `, delay),
            ]);
            delay += step;

            // kv rows
            if (ok) {
                svc.rows(d).forEach(({ k, v, cls: vc }) => {
                    appendLines([
                        line('out-line ln-kv', `
                            <span class="kv-key">${k}</span>
                            <span class="kv-eq">=</span>
                            <span class="kv-val ${vc ?? ''}">${v ?? '—'}</span>
                        `, delay),
                    ]);
                    delay += step * 0.6;
                });
            } else if (d.error) {
                appendLines([
                    line('out-line ln-error-msg', `└─ ${d.error}`, delay),
                ]);
                delay += step;
            }

            appendLines([line('out-line ln-blank', '', delay)]);
            delay += step / 2;
        });

        // summary
        appendLines([
            line('out-line ln-summary', `
                done — <span class="ok-count">${okCount} ok</span>${errCount > 0 ? `, <span class="error-count">${errCount} failed</span>` : ''}
            `, delay),
        ]);

        cursor.style.display = 'none';

    } catch (e) {
        const out = document.getElementById('output');
        out.innerHTML = '';
        appendLines([
            line('out-line', `<span style="color:var(--red)">✗ connection refused — ${e.message}</span>`, 0),
        ]);
        cursor.style.display = 'none';
    } finally {
        btn.classList.remove('spinning');
        btn.disabled = false;
    }
}

loadHealth();
</script>
</body>
</html>
