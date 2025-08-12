@props(['label', 'name', 'type' => 'text', 'required' => false])

<div>
    <label class="block text-sm font-medium text-gray-700" for="{{ $name }}">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500']) }}
    />
</div>
