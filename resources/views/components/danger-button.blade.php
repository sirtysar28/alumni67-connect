<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded px-5 py-2.5 text-sm font-semibold transition bg-red-500/90 text-white hover:bg-red-500 active:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 focus:ring-offset-navy-deep disabled:opacity-25']) }}>
    {{ $slot }}
</button>
