<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 bg-destructive text-destructive-foreground rounded-md text-sm font-medium px-4 py-2 hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 active:scale-[0.98] transition-all duration-200 disabled:pointer-events-none disabled:opacity-50']) }}>
    {{ $slot }}
</button>
