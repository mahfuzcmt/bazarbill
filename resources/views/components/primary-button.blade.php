<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary px-5 py-2.5 text-sm']) }}>
    {{ $slot }}
</button>
