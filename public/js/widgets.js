/* ============================================================
   WIDGETS JS — Alumni67 Connect (statis, tanpa build)
   1. notifPoll()  — refresh ikon lonceng 🔔 (badge + dropdown)
   2. ChatWidget   — chat antar alumni 💬 (fetch + polling ringan)
   Dipanggil otomatis via DOMContentLoaded di bawah.
   ============================================================ */
(function () {
    'use strict';

    var CSRF = window.csrfToken;
    function post(url) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
    }
    function getJSON(url) {
        return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); });
    }
    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    /* ================= 1. NOTIFIKASI 🔔 ================= */
    window.notifPoll = function () {
        var badge = document.getElementById('notif-badge');
        var list = document.getElementById('notif-list');
        if (!badge) return; // guest / belum login

        getJSON('/notifikasi/poll').then(function (data) {
            // Badge
            var n = data.unread || 0;
            badge.textContent = n > 99 ? '99+' : n;
            if (n > 0) {
                if (!badge.classList.contains('show')) badge.classList.add('pulse');
                badge.classList.add('show');
                setTimeout(function () { badge.classList.remove('pulse'); }, 500);
            } else {
                badge.classList.remove('show');
            }

            // Isi dropdown (bila panel kebuka / selalu supaya segar)
            // Catatan: ready=false → tabel belum ada, biarkan petunjuk server-side tampil.
            if (list && data.latest && data.ready !== false) {
                if (!data.latest.length) {
                    list.innerHTML = '<div class="notif-empty">Belum ada notifikasi 🔔</div>';
                    return;
                }
                list.innerHTML = data.latest.map(function (x) {
                    return '<a href="/notifikasi/' + encodeURIComponent(x.id) + '/read" class="notif-item' + (x.read ? '' : ' unread') + '">'
                        + '<span class="n-icon">' + esc(x.icon) + '</span>'
                        + '<span class="min-w-0">'
                        + '<span class="n-title block">' + esc(x.title) + '</span>'
                        + '<span class="n-msg block">' + esc(x.message) + '</span>'
                        + '<span class="n-when block">' + esc(x.when) + '</span>'
                        + '</span></a>';
                }).join('');
            }
        }).catch(function () { /* senyap */ });
    };

    // Refresh tiap 30 detik + saat tab jadi aktif lagi
    setInterval(window.notifPoll, 30000);
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) window.notifPoll();
    });

    /* ================= 2. CHAT 💬 ================= */
    var root = document.getElementById('chat-widget');
    if (!root) return; // guest / belum login

    var S = {
        contactsUrl: root.dataset.contactsUrl,
        peerId: parseInt(root.dataset.peer || '0', 10) || null,
        autoOpen: root.dataset.autoOpen === '1',
        contacts: [],
        peer: null,          // {id,name,kelas,initial}
        messages: [],
        lastId: 0,
        pollTimer: null,
        contactsTimer: null,
        sending: false
    };

    var fab = document.getElementById('chat-fab');
    var panel = document.getElementById('chat-panel');
    var viewContacts = document.getElementById('chat-view-contacts');
    var viewThread = document.getElementById('chat-view-thread');
    var contactsBox = document.getElementById('chat-contacts');
    var searchInput = document.getElementById('chat-search');
    var msgsBox = document.getElementById('chat-msgs');
    var typing = document.getElementById('chat-typing');
    var form = document.getElementById('chat-form');
    var bodyInput = document.getElementById('chat-body');
    var sendBtn = document.getElementById('chat-send-btn');

    /* ---------- buka/tutup ---------- */
    function openPanel() {
        panel.classList.add('open');
        loadContacts();
        restartContactPolling();
        if (S.peerId) openThread(S.peerId);
    }
    function closePanel() {
        panel.classList.remove('open');
        stopPolling();
    }
    function togglePanel() {
        panel.classList.contains('open') ? closePanel() : openPanel();
    }
    fab.addEventListener('click', togglePanel);
    document.getElementById('chat-close-1').addEventListener('click', closePanel);
    document.getElementById('chat-close-2').addEventListener('click', closePanel);
    document.getElementById('chat-back').addEventListener('click', function () {
        viewThread.style.display = 'none';
        viewContacts.style.display = 'block';
        S.peer = null;
        stopPolling();
        loadContacts(); // refresh badge unread kontak
    });

    /* ---------- kontak ---------- */
    function loadContacts() {
        return getJSON(S.contactsUrl).then(function (data) {
            S.contacts = data.contacts || [];
            renderContacts(searchInput.value || '');
            updateFabBadge();
        }).catch(function () {
            contactsBox.innerHTML = '<div class="chat-empty">Gagal memuat kontak.</div>';
        });
    }

    function renderContacts(q) {
        q = q.toLowerCase().trim();
        var list = S.contacts.filter(function (c) {
            return !q || c.name.toLowerCase().indexOf(q) !== -1 || (c.kelas || '').toLowerCase().indexOf(q) !== -1;
        });
        if (!list.length) {
            contactsBox.innerHTML = '<div class="chat-empty">' + (q ? 'Tidak ada alumni cocok “' + esc(q) + '”.' : 'Belum ada alumni lain.') + '</div>';
            return;
        }
        contactsBox.innerHTML = list.map(function (c) {
            return '<button type="button" class="chat-contact" data-id="' + c.id + '">'
                + '<span class="c-ava">' + esc(c.initial)
                + (c.unread > 0 ? '<span class="c-dot">' + (c.unread > 9 ? '9+' : c.unread) + '</span>' : '')
                + '</span>'
                + '<span class="min-w-0">'
                + '<span class="c-name block">' + esc(c.name) + (c.kelas ? ' <span style="font-weight:400;color:rgb(var(--c-cream-dim))">· ' + esc(c.kelas) + '</span>' : '') + '</span>'
                + '<span class="c-last block">' + (c.last ? esc(c.last) : '<i style="opacity:.55">mulai chat →</i>') + '</span>'
                + '</span></button>';
        }).join('');
        Array.prototype.forEach.call(contactsBox.querySelectorAll('.chat-contact'), function (btn) {
            btn.addEventListener('click', function () { openThread(parseInt(btn.dataset.id, 10)); });
        });
    }

    searchInput.addEventListener('input', function () { renderContacts(searchInput.value); });

    function updateFabBadge() {
        var total = S.contacts.reduce(function (a, c) { return a + (c.unread || 0); }, 0);
        var badge = document.getElementById('chat-badge');
        badge.textContent = total > 99 ? '99+' : total;
        badge.classList.toggle('show', total > 0);
    }

    /* ---------- percakapan ---------- */
    function openThread(peerId) {
        var c = S.contacts.filter(function (x) { return x.id === peerId; })[0];
        S.peer = c ? { id: c.id, name: c.name, kelas: c.kelas, initial: c.initial } : { id: peerId, name: '…', kelas: '', initial: '?' };
        S.messages = [];
        S.lastId = 0;

        document.getElementById('chat-peer-ava').textContent = S.peer.initial;
        document.getElementById('chat-peer-name').textContent = S.peer.name;
        document.getElementById('chat-peer-sub').textContent = S.peer.kelas ? 'Kelas ' + S.peer.kelas : 'Alumni';

        viewContacts.style.display = 'none';
        viewThread.style.display = 'flex';
        msgsBox.innerHTML = '<div class="chat-info">Memuat percakapan…</div>';

        getJSON('/chat/' + peerId).then(function (data) {
            S.peer = data.peer;
            document.getElementById('chat-peer-ava').textContent = data.peer.initial;
            document.getElementById('chat-peer-name').textContent = data.peer.name;
            document.getElementById('chat-peer-sub').textContent = data.peer.kelas ? 'Kelas ' + data.peer.kelas : 'Alumni';
            S.messages = data.messages || [];
            S.lastId = S.messages.length ? S.messages[S.messages.length - 1].id : 0;
            renderMessages(true);
            restartMessagePolling();
            setTimeout(function () { bodyInput.focus(); }, 100);
        }).catch(function () {
            msgsBox.innerHTML = '<div class="chat-info">Gagal memuat percakapan.</div>';
        });
    }

    function renderMessages(scroll) {
        if (!S.messages.length) {
            msgsBox.innerHTML = '<div class="chat-info">Belum ada pesan.<br>Sapa «' + esc(S.peer.name) + '» dengan pesan pertama! 👋</div>';
            return;
        }
        var html = '', lastDate = '';
        S.messages.forEach(function (m) {
            if (m.date !== lastDate) {
                html += '<div class="chat-day">' + esc(m.date) + '</div>';
                lastDate = m.date;
            }
            html += '<div class="chat-msg ' + (m.mine ? 'mine' : 'theirs') + '">'
                + '<div class="b">' + esc(m.body) + '</div>'
                + '<div class="t">' + esc(m.when) + (m.mine && m.read ? ' ✓✓' : (m.mine ? ' ✓' : '')) + '</div>'
                + '</div>';
        });
        msgsBox.innerHTML = html;
        if (scroll) msgsBox.scrollTop = msgsBox.scrollHeight;
    }

    function appendMessage(m) {
        var nearBottom = msgsBox.scrollHeight - msgsBox.scrollTop - msgsBox.clientHeight < 80;
        S.messages.push(m);
        if (m.id > S.lastId) S.lastId = m.id;
        renderMessages(nearBottom || m.mine);
    }

    /* ---------- kirim ---------- */
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var body = bodyInput.value.trim();
        if (!body || S.sending || !S.peer) return;
        S.sending = true;
        sendBtn.disabled = true;
        typing.classList.add('show');

        fetch('/chat/' + S.peer.id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ body: body })
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.ok && data.message) appendMessage(data.message);
            bodyInput.value = '';
            bodyInput.style.height = 'auto';
        }).catch(function () {
            alert('Pesan gagal terkirim. Coba lagi.');
        }).finally(function () {
            S.sending = false;
            sendBtn.disabled = false;
            typing.classList.remove('show');
            bodyInput.focus();
        });
    });

    // Enter kirim · Shift+Enter baris baru · auto-grow
    bodyInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit'));
        }
    });
    bodyInput.addEventListener('input', function () {
        bodyInput.style.height = 'auto';
        bodyInput.style.height = Math.min(bodyInput.scrollHeight, 80) + 'px';
    });

    /* ---------- polling ringan ---------- */
    function restartMessagePolling() {
        stopPolling();
        S.pollTimer = setInterval(function () {
            if (!S.peer) return;
            getJSON('/chat/' + S.peer.id + '/poll?after=' + S.lastId).then(function (data) {
                (data.messages || []).forEach(appendMessage);
            }).catch(function () { /* senyap */ });
        }, 5000);
    }
    function restartContactPolling() {
        if (S.contactsTimer) clearInterval(S.contactsTimer);
        S.contactsTimer = setInterval(function () {
            if (!panel.classList.contains('open')) return;
            if (!S.peer) loadContacts(); // di daftar kontak: refresh unread
        }, 15000);
    }
    function stopPolling() {
        if (S.pollTimer) { clearInterval(S.pollTimer); S.pollTimer = null; }
    }

    /* ---------- init ---------- */
    window.notifPoll(); // langsung sinkronkan lonceng
    loadContacts();     // muat kontak di belakang layar → badge unread 💬 langsung tampil
    if (S.autoOpen) {
        openPanel();
    } else if (S.peerId) {
        // dibuka via link chat dari halaman lain (?with=...) → buka panel
        openPanel();
    }
})();
