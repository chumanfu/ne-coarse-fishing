{{--
    Weight inputs for one catch row.

    Rendered inside an Alpine `x-for` over `catches`, so `c` and `index` are
    runtime values. Both unit variants stay in the DOM and post together; the
    controller reads whichever `entered_unit` says was used.

    $label - text above the inputs (differs for a single fish vs a bag total).
--}}
<div>
    <label class="block text-sm font-semibold mb-1">{{ $label ?? 'Weight' }}</label>

    <input type="hidden" :name="`catches[${index}][entered_unit]`" :value="weightUnit">

    <div x-show="weightUnit === 'lb_oz'" class="flex items-center gap-2">
        <div class="flex items-center gap-1.5">
            <input type="number"
                   inputmode="numeric"
                   min="0"
                   step="1"
                   :name="`catches[${index}][weight_lb]`"
                   x-model="c.weight_lb"
                   placeholder="0"
                   aria-label="Pounds"
                   class="w-20 min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
            <span class="text-sm font-semibold text-slate-600">lb</span>
        </div>
        <div class="flex items-center gap-1.5">
            <input type="number"
                   inputmode="decimal"
                   min="0"
                   max="15.99"
                   step="0.25"
                   :name="`catches[${index}][weight_oz]`"
                   x-model="c.weight_oz"
                   placeholder="0"
                   aria-label="Ounces"
                   class="w-20 min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
            <span class="text-sm font-semibold text-slate-600">oz</span>
        </div>
    </div>

    <div x-show="weightUnit === 'kg'" x-cloak class="flex items-center gap-1.5">
        <input type="number"
               inputmode="decimal"
               min="0"
               step="0.01"
               :name="`catches[${index}][weight_kg]`"
               x-model="c.weight_kg"
               placeholder="0.00"
               aria-label="Kilograms"
               class="w-28 min-h-11 rounded-md border-2 border-slate-400 focus:border-sky-700 focus:ring-sky-700">
        <span class="text-sm font-semibold text-slate-600">kg</span>
    </div>

    <p class="text-xs text-slate-500 mt-1" x-show="conversionHint(c)" x-cloak>
        <span x-text="conversionHint(c)"></span>
    </p>
</div>
