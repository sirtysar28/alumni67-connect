@props(['url', 'title'])

{{--
    Tombol bagikan ke medsos (WhatsApp, Facebook, X, Telegram) + salin link.
    Dipakai di halaman detail event & berita.
--}}
<div class="mt-4 flex flex-wrap items-center gap-2"
     x-data="{ copied: false, url: @js($url), doCopy() {
         try {
             navigator.clipboard.writeText(this.url);
         } catch (e) {
             var i = document.createElement('input');
             i.value = this.url; document.body.appendChild(i);
             i.select(); document.execCommand('copy'); i.remove();
         }
         this.copied = true;
         setTimeout(() => this.copied = false, 2000);
     } }">
    <span class="font-mono text-[10px] uppercase tracking-widest text-creamDim">📤 Bagikan:</span>

    <a href="https://wa.me/?text={{ rawurlencode($title."\n".$url) }}"
       target="_blank" rel="noopener"
       class="btn-outline !px-3 !py-1.5 text-xs">💬 WhatsApp</a>

    <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}"
       target="_blank" rel="noopener"
       class="btn-outline !px-3 !py-1.5 text-xs">📘 Facebook</a>

    <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($title) }}&url={{ rawurlencode($url) }}"
       target="_blank" rel="noopener"
       class="btn-outline !px-3 !py-1.5 text-xs">𝕏 Tweet</a>

    <a href="https://t.me/share/url?url={{ rawurlencode($url) }}&text={{ rawurlencode($title) }}"
       target="_blank" rel="noopener"
       class="btn-outline !px-3 !py-1.5 text-xs">✈️ Telegram</a>

    <button type="button" class="btn-outline !px-3 !py-1.5 text-xs"
            @click="doCopy()"
            :class="copied && 'hover:border-neon hover:text-neon'"
            x-text="copied ? '✓ Link tersalin!' : '🔗 Salin link'">🔗 Salin link</button>
</div>
