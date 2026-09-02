@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800']) }}>
        <div class="font-semibold">Check the form and try again</div>
        <ul class="mt-2 list-disc space-y-1 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
