@php $isMobile = request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone/i', request()->header('User-Agent')); @endphp
@if(!$isMobile)
<canvas id="particles-canvas"
        class="fixed inset-0 pointer-events-none z-0 opacity-40"
        aria-hidden="true">
</canvas>
@endif
