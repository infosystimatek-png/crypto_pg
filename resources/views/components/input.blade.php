@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'mt-1.5 block w-full rounded-xl border-slate-200 bg-slate-50 text-slate-900 shadow-none transition focus:border-indigo-500 focus:bg-white focus:ring-indigo-500']) !!}>
