<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded px-5 py-2.5 text-sm font-semibold transition bg-neon text-navy-deep hover:bg-[#4dff4d] hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-neon focus:ring-offset-2 focus:ring-offset-navy-deep']) }}>
    {{ $slot }}
</button>
