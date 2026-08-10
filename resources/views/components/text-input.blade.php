@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 text-slate-900 focus:border-sky-700 focus:ring-sky-700 rounded-md shadow-sm dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-400']) }}>
