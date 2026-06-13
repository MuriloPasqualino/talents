@php
    $lines = preg_split('/\r?\n/', (string) $text);
    $inList = false;
@endphp
@foreach($lines as $line)
    @php
        $trimmed = ltrim($line);
        $isBullet = $trimmed !== '' && (str_starts_with($trimmed, '•') || str_starts_with($trimmed, '-'));
    @endphp
    @if($isBullet)
        @if(!$inList)
            <ul class="desc-bullets">
            @php $inList = true; @endphp
        @endif
        <li>{{ ltrim(substr($trimmed, 1)) }}</li>
    @else
        @if($inList)
            </ul>
            @php $inList = false; @endphp
        @endif
        @if($trimmed !== '')
            <p class="desc-paragraph">{{ $trimmed }}</p>
        @endif
    @endif
@endforeach
@if($inList)
    </ul>
@endif
