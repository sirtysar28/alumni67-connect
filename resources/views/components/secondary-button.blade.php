<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 rounded px-5 py-2.5 text-sm font-semibold transition border border-line/40 text-creamDim hover:border-neon hover:text-neon focus:outline-none focus:ring-2 focus:ring-neon/60 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
