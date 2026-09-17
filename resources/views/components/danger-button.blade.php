<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-danger px-4 py-2 text-sm']) }}>
    {{ $slot }}
</button>
