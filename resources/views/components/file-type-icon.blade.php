@props([
    'ext' => '',
    'alpineExt' => null,
    'size' => 'sm',
])

@php
    $sizeClasses = match ($size) {
        'md' => 'w-6 h-7',
        'xs' => 'w-[18px] h-[21px]',
        default => 'w-[21px] h-6',
    };

    $normalizedExt = strtolower(trim((string) $ext));

    $colorClass = match ($normalizedExt) {
        'pdf' => 'text-red-600',
        'doc', 'docx' => 'text-blue-600',
        'xls', 'xlsx' => 'text-emerald-600',
        'ppt', 'pptx' => 'text-amber-600',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'text-purple-600',
        default => 'text-slate-600',
    };

    $label = match ($normalizedExt) {
        'pdf' => 'PDF',
        'doc', 'docx' => 'DOC',
        'xls', 'xlsx' => 'XLS',
        'ppt', 'pptx' => 'PPT',
        'jpg', 'jpeg' => 'JPG',
        'png' => 'PNG',
        'gif' => 'GIF',
        'webp' => 'IMG',
        default => $normalizedExt !== '' ? strtoupper(substr($normalizedExt, 0, 4)) : 'FILE',
    };

    $title = match ($normalizedExt) {
        'pdf' => 'Dokumen PDF',
        'doc' => 'Microsoft Word (DOC)',
        'docx' => 'Microsoft Word (DOCX)',
        'xls' => 'Microsoft Excel (XLS)',
        'xlsx' => 'Microsoft Excel (XLSX)',
        'ppt' => 'Microsoft PowerPoint (PPT)',
        'pptx' => 'Microsoft PowerPoint (PPTX)',
        'jpg', 'jpeg', 'png', 'gif', 'webp' => 'Gambar ' . strtoupper($normalizedExt),
        default => $normalizedExt !== '' ? strtoupper($normalizedExt) : 'Berkas',
    };

    $alpineExpr = $alpineExt ? "({$alpineExt} || '').toString().toLowerCase().trim()" : null;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center flex-shrink-0 select-none' . ($alpineExpr ? '' : ' ' . $colorClass)]) }}
      @if($alpineExpr)
          :class="{!! $alpineExpr !!} === 'pdf' ? 'text-red-600' : (['doc', 'docx'].includes({!! $alpineExpr !!}) ? 'text-blue-600' : (['xls', 'xlsx'].includes({!! $alpineExpr !!}) ? 'text-emerald-600' : (['ppt', 'pptx'].includes({!! $alpineExpr !!}) ? 'text-amber-600' : (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes({!! $alpineExpr !!}) ? 'text-purple-600' : 'text-slate-600'))))"
          :title="({pdf:'Dokumen PDF',doc:'Microsoft Word (DOC)',docx:'Microsoft Word (DOCX)',xls:'Microsoft Excel (XLS)',xlsx:'Microsoft Excel (XLSX)',ppt:'Microsoft PowerPoint (PPT)',pptx:'Microsoft PowerPoint (PPTX)',jpg:'Gambar JPG',jpeg:'Gambar JPEG',png:'Gambar PNG',gif:'Gambar GIF',webp:'Gambar WEBP'})[{!! $alpineExpr !!}] || ({!! $alpineExpr !!} ? {!! $alpineExpr !!}.toUpperCase() : 'Berkas')"
      @else
          title="{{ $title }}"
      @endif>
    <svg class="{{ $sizeClasses }}" viewBox="0 0 28 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" @if(!$alpineExpr) aria-label="{{ $title }}" @else aria-hidden="true" @endif>
        <path d="M4.5 1H17.5V8.3C17.5 8.96274 18.0373 9.5 18.7 9.5H26V28.5C26 29.8807 24.8807 31 23.5 31H4.5C3.11929 31 2 29.8807 2 28.5V3.5C2 2.11929 3.11929 1 4.5 1Z" fill="currentColor"/>
        <path d="M19.3 1L26 7.7H19.3V1Z" fill="currentColor"/>
        @if($alpineExpr)
            <text x="14"
                  y="22.6"
                  text-anchor="middle"
                  fill="#FFFFFF"
                  font-family="Inter, 'Segoe UI', Arial, sans-serif"
                  font-size="8.4"
                  font-weight="700"
                  letter-spacing="0.2"
                  x-text="({pdf:'PDF',doc:'DOC',docx:'DOC',xls:'XLS',xlsx:'XLS',ppt:'PPT',pptx:'PPT',jpg:'JPG',jpeg:'JPG',png:'PNG',gif:'GIF',webp:'IMG'})[{!! $alpineExpr !!}] || ({!! $alpineExpr !!} ? {!! $alpineExpr !!}.toUpperCase().slice(0, 4) : 'FILE')"></text>
        @else
            <text x="14"
                  y="22.6"
                  text-anchor="middle"
                  fill="#FFFFFF"
                  font-family="Inter, 'Segoe UI', Arial, sans-serif"
                  font-size="{{ strlen($label) > 3 ? '7.0' : '8.4' }}"
                  font-weight="700"
                  letter-spacing="0.2">{{ $label }}</text>
        @endif
    </svg>
</span>
