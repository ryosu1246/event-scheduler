@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};

$dropdownId = 'dropdown-' . uniqid();
@endphp

<div class="relative" id="{{ $dropdownId }}">
    <div class="dropdown-trigger cursor-pointer">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }} hidden transition ease-out duration-200 opacity-0 scale-95"
         style="transform-origin: top right;">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

<script>
(function() {
    const dropdown = document.getElementById('{{ $dropdownId }}');
    const trigger = dropdown.querySelector('.dropdown-trigger');
    const menu = dropdown.querySelector('.dropdown-menu');

    function openMenu() {
        menu.classList.remove('hidden', 'opacity-0', 'scale-95');
        menu.classList.add('opacity-100', 'scale-100');
    }

    function closeMenu() {
        menu.classList.add('opacity-0', 'scale-95');
        menu.classList.remove('opacity-100', 'scale-100');
        setTimeout(() => menu.classList.add('hidden'), 150);
    }

    function isOpen() {
        return !menu.classList.contains('hidden');
    }

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        if (isOpen()) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && isOpen()) {
            closeMenu();
        }
    });

    menu.addEventListener('click', function() {
        closeMenu();
    });
})();
</script>
