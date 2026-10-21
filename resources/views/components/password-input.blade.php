@props([
    'id',
    'name',
    'label',
    'placeholder' => null,
    'autocomplete' => 'current-password',
    'required' => false,
    'autofocus' => false,
    'invalid' => false,
    'inputClass' => 'block w-full rounded-xl border-slate-200 py-3 pl-11 pr-12 text-sm shadow-sm placeholder:text-slate-400 focus:border-slate-400 focus:ring-slate-300',
])

<div>
    <x-input-label :for="$id" :value="$label" class="mb-2 text-sm font-semibold text-slate-700" />
    <div class="relative">
        @if (isset($leadingIcon))
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                {{ $leadingIcon }}
            </span>
        @endif
        <x-text-input :id="$id" :name="$name" type="password" :required="$required" :autofocus="$autofocus"
            :autocomplete="$autocomplete" :placeholder="$placeholder"
            :aria-invalid="$invalid ? 'true' : 'false'" data-password-input class="{{ $inputClass }}" />
        <button type="button"
            class="absolute right-3 top-1/2 inline-flex -translate-y-1/2 items-center justify-center text-slate-400 transition hover:text-slate-700 focus:outline-none focus:text-slate-700"
            data-password-toggle data-password-target="{{ $id }}" data-password-label="{{ strtolower($label) }}"
            aria-label="Show {{ strtolower($label) }}" aria-pressed="false">
            <x-heroicon-o-eye class="hidden h-5 w-5" data-password-eye aria-hidden="true" />
            <x-heroicon-o-eye-slash class="h-5 w-5" data-password-eye-slash aria-hidden="true" />
        </button>
    </div>
</div>
