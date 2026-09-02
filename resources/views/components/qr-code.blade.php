@props(['data', 'size' => 180])

@php
    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle((int) $size, 1),
        new \BaconQrCode\Renderer\Image\SvgImageBackEnd(),
    );
    $svg = (new \BaconQrCode\Writer($renderer))->writeString($data);
    $svg = preg_replace('/<\?xml.*?\?>/', '', $svg) ?? $svg;
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex rounded-2xl bg-white p-3 ring-1 ring-slate-200']) }}>
    {!! $svg !!}
</div>
