<label class="flex items-center justify-between gap-3 cursor-pointer min-h-[48px]">
    <span class="text-text-primary text-sm">{{ $label }}</span>
    <span class="relative inline-flex items-center flex-shrink-0">
        <input type="checkbox" class="peer sr-only" x-model="{{ $model }}"
               @if(isset($onChange)) @change="{{ $onChange }}" @endif>
        <span class="w-11 h-6 rounded-full bg-bg-primary border border-accent/30
                     peer-checked:bg-accent peer-checked:border-accent transition-colors duration-200"></span>
        <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-text-secondary
                     peer-checked:bg-bg-primary peer-checked:translate-x-5 transition-transform duration-200"></span>
    </span>
</label>
