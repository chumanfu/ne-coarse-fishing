import { OLIVETTES, SHOT, fillWeight, shotsBetween, totalGrams } from './shots';

export const MIN_DEPTH_CM = 30;

export function groupGrams(g) {
    return totalGrams(g.items) + (g.olivetteGrams ?? 0);
}

function clamp(n, min, max) {
    return Math.max(min, Math.min(max, n));
}

/** Keeps a shot between just above the hook and just below the float. */
function onLine(h, depthCm) {
    return clamp(h, 5, depthCm - 10);
}

/** Position of the shot nearest the hook (the tell-tale), usually 4–8in up. */
function lowestShot(depthCm) {
    return onLine(clamp(depthCm * 0.12, 10, 20), depthCm);
}

/** `n` positions from `from` to `to` inclusive. */
function evenly(n, from, to) {
    if (n <= 1) return [from];

    return Array.from({ length: n }, (_, i) => from + (i * (to - from)) / (n - 1));
}

/** Small shot used to dot the tip down once the main pattern is on. */
function trimItems(remaining, maxGrams) {
    if (remaining < SHOT.No13.grams) return [];

    return fillWeight(remaining, shotsBetween(SHOT.No13.grams, maxGrams));
}

function poleDropper(fg) {
    if (fg < 0.15) return SHOT.No12;
    if (fg < 0.3) return SHOT.No11;
    if (fg < 0.6) return SHOT.No10;
    if (fg < 1) return SHOT.No9;
    if (fg < 2) return SHOT.No8;

    return SHOT.No6;
}

function finish(base, input) {
    const groups = [...base.groups].sort((a, b) => b.heightCm - a.heightCm);

    return {
        ...base,
        groups,
        floatGrams: input.floatGrams,
        loadGrams: groups.reduce((sum, g) => sum + groupGrams(g), 0),
        recommended: false,
    };
}

function strung(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const candidates = [SHOT.No4, SHOT.No6, SHOT.No8, SHOT.No9, SHOT.No10, SHOT.No11, SHOT.No12];
    const shot = candidates.find((s) => fg / s.grams >= 4) ?? SHOT.No12;
    const count = clamp(Math.floor((fg + 1e-6) / shot.grams), 1, 12);
    const lowest = lowestShot(d);
    const top = onLine(Math.max(lowest + 10, d * 0.6), d);
    const groups = evenly(count, lowest, top).map((h) => ({
        role: 'strung',
        heightCm: h,
        items: [{ shot, count: 1 }],
    }));
    const trim = trimItems(fg - count * shot.grams, shot.grams * 0.99);
    if (trim.length) {
        groups.push({ role: 'trim', heightCm: onLine(top + 8, d), items: trim, note: 'Tip-dotting shot' });
    }

    return finish(
        {
            id: 'strung',
            name: 'Strung out',
            summary: `${count} × ${shot.label} spaced evenly through the bottom of the rig`,
            whenToUse:
                'Shallow water, fish feeding up in the water, or when you want a slow, natural fall of the hookbait.',
            groups,
            tips: [
                'Watch the tip settle as each shot registers – if it settles early, a fish has probably intercepted the bait.',
                'Ideal for casters, maggots and hemp fished on the drop.',
                'Close the gaps between shot to speed up the fall if small fish are a nuisance.',
            ],
        },
        input,
    );
}

function bulkDroppers(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const dropper = poleDropper(fg);
    let n = d < 100 ? 2 : 3;
    while (n > 1 && n * dropper.grams > fg * 0.45) n--;

    const lowest = lowestShot(d);
    let bulkH = onLine(clamp(d * 0.35, 30, 90), d);
    if (bulkH <= lowest + 5) bulkH = onLine(lowest + 15, d);

    const bulkTarget = fg - n * dropper.grams;
    const bulkMax = fg < 0.3 ? bulkTarget / 2 : bulkTarget / 3;
    const bulk = fillWeight(bulkTarget, shotsBetween(dropper.grams, Math.max(dropper.grams, bulkMax)));
    const trim = trimItems(bulkTarget - totalGrams(bulk), dropper.grams * 0.99);

    const groups = [
        {
            role: 'bulk',
            heightCm: bulkH,
            items: [...bulk, ...trim],
            note: 'Bunch together, or spread over an inch or so',
        },
    ];
    for (let i = 0; i < n; i++) {
        groups.push({
            role: 'dropper',
            heightCm: lowest + (i * (bulkH - lowest)) / n,
            items: [{ shot: dropper, count: 1 }],
        });
    }

    return finish(
        {
            id: 'bulk_droppers',
            name: 'Bulk & droppers',
            summary: `Main bulk part-way down, ${n} × ${dropper.label} droppers below it`,
            whenToUse:
                'Deeper water, flowing water or when small fish intercept the bait – the bulk gets the hookbait down fast.',
            groups,
            tips: [
                'Move the bulk closer to the hook to get through small fish; further up for a slower final drop.',
                'The lowest dropper usually sits at the hooklength loop.',
                'If bites come on the drop, swap to a strung pattern.',
            ],
        },
        input,
    );
}

/** Olivette sizes that leave room for at least one dropper on this float. */
export function olivetteOptions(floatGrams) {
    return OLIVETTES.filter((o) => floatGrams - o >= SHOT.No12.grams - 1e-6);
}

function olivette(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const options = olivetteOptions(fg);
    if (!options.length) return null;

    const base = poleDropper(fg).grams < SHOT.No10.grams ? SHOT.No10 : poleDropper(fg);
    const maxDroppers = d > 250 ? 4 : 3;
    // Auto: the largest olivette that still leaves room for a full set of droppers.
    const auto = [...options].reverse().find((o) => o <= fg - maxDroppers * base.grams + 1e-6) ?? options[0];
    const olive = input.olivetteGrams && options.includes(input.olivetteGrams) ? input.olivetteGrams : auto;

    const rem = fg - olive;
    const dropper =
        [base, SHOT.No10, SHOT.No11, SHOT.No12].find((s) => s.grams <= base.grams && rem / s.grams >= 1 - 1e-6) ??
        SHOT.No12;
    const n = clamp(Math.floor((rem + 1e-6) / dropper.grams), 1, maxDroppers);
    // A small olivette leaves extra weight: carry it as shot just under the olivette.
    const leftover = rem - n * dropper.grams;
    const extra =
        leftover > dropper.grams
            ? fillWeight(leftover, shotsBetween(SHOT.No13.grams, Math.max(SHOT.No13.grams, leftover)))
            : trimItems(leftover, dropper.grams * 0.99);

    const lowest = lowestShot(d);
    let olH = onLine(clamp(d * 0.3, 40, 100), d);
    if (olH <= lowest + 5) olH = onLine(lowest + 15, d);

    const groups = [
        {
            role: 'olivette',
            heightCm: olH,
            olivetteGrams: olive,
            items: extra,
            note: extra.length ? 'Olivette, with extra shot just beneath it' : 'Olivette',
        },
    ];
    for (let i = 0; i < n; i++) {
        groups.push({
            role: 'dropper',
            heightCm: lowest + (i * (olH - lowest)) / n,
            items: [{ shot: dropper, count: 1 }],
        });
    }

    return finish(
        {
            id: 'olivette',
            name: 'Olivette',
            summary: `${olive}g olivette with ${n} × ${dropper.label} droppers`,
            whenToUse:
                'Deep water (6ft+) or heavier pole floats, especially in wind or tow – the most stable pole rig.',
            groups,
            tips: [
                'Lock the olivette with a small shot or silicone stop either side.',
                'Use an in-line olivette threaded on the main line, not on the hooklength.',
                'Lay the rig in so it falls in a straight line from float to hook.',
            ],
        },
        input,
    );
}

function shirtButton(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const candidates = [SHOT.No4, SHOT.No6, SHOT.No8, SHOT.No9, SHOT.No10];
    const shot = candidates.find((s) => fg / s.grams >= 6) ?? SHOT.No10;
    const count = clamp(Math.floor((fg + 1e-6) / shot.grams), 1, 14);
    const lowest = lowestShot(d);
    const top = onLine(Math.max(lowest + 10, d * 0.85), d);
    const groups = evenly(count, lowest, top).map((h) => ({
        role: 'strung',
        heightCm: h,
        items: [{ shot, count: 1 }],
    }));
    const trim = trimItems(fg - count * shot.grams, shot.grams * 0.99);
    if (trim.length) {
        groups.push({ role: 'trim', heightCm: onLine(top + 5, d), items: trim, note: 'Tip-dotting shot' });
    }

    return finish(
        {
            id: 'shirt_button',
            name: 'Shirt button',
            summary: `${count} × ${shot.label} evenly spaced from float to hook`,
            whenToUse:
                'Classic stick float pattern for steady, walking-pace water up to about 5ft – fish taking bait as it falls.',
            groups,
            tips: [
                'Hold back gently so the bait leads the float down the swim.',
                'Push the lower shot closer together as the swim gets deeper or faster.',
                'Feed little and often – loose feed and hookbait should fall together.',
            ],
        },
        input,
    );
}

function wagglerDropper(fg) {
    if (fg < 0.8) return SHOT.No10;
    if (fg <= 1.5) return SHOT.No8;
    if (fg <= 3) return SHOT.No6;

    return SHOT.No4;
}

/**
 * Locking shot has to be small enough to sit either side of the float.
 * A 1g waggler must not get 2 × BB (0.80g) as locking shot – that would cock it on its own.
 */
function lockingItems(floatGrams, lockTarget) {
    const maxShot = Math.max(SHOT.No13.grams, Math.min(lockTarget / 4, floatGrams * 0.2));

    return fillWeight(lockTarget, shotsBetween(SHOT.No13.grams, maxShot));
}

function wagglerLocking(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const dropper = wagglerDropper(fg);
    let n = d < 120 ? 2 : 3;
    while (n > 1 && n * dropper.grams > fg * 0.25) n--;
    const lockTarget = fg - n * dropper.grams;
    const lock = lockingItems(fg, lockTarget);

    const lowest = lowestShot(d);
    const top = onLine(Math.max(lowest + 10, d * 0.33), d);
    const groups = [
        {
            role: 'locking',
            heightCm: d,
            items: lock,
            note: 'Split either side of the float – about two-thirds below, one-third above',
        },
        ...evenly(n, lowest, top).map((h) => ({
            role: 'dropper',
            heightCm: h,
            items: [{ shot: dropper, count: 1 }],
        })),
    ];

    return finish(
        {
            id: 'waggler_locking',
            name: 'Locking + droppers',
            summary: `Locking shot around the float, ${n} × ${dropper.label} down the line`,
            whenToUse: 'The standard waggler set-up for stillwaters and slow rivers from about 4ft deep.',
            groups,
            tips: [
                'Sink the line after casting: overcast, then dip the rod tip and wind back sharply.',
                'Keep most of the weight at the float for casting distance and accuracy.',
                'Move the droppers up the line if fish are feeding off the bottom.',
            ],
        },
        input,
    );
}

function wagglerDrop(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const stringTarget = fg * 0.3;
    const candidates = [SHOT.No4, SHOT.No6, SHOT.No8, SHOT.No9, SHOT.No10, SHOT.No11];
    const shot = candidates.find((s) => stringTarget / s.grams >= 3) ?? SHOT.No11;
    const count = clamp(Math.floor((stringTarget + 1e-6) / shot.grams), 1, 8);
    const lockTarget = fg - count * shot.grams;
    const lock = lockingItems(fg, lockTarget);

    const lowest = lowestShot(d);
    const top = onLine(Math.max(lowest + 10, d * 0.6), d);
    const groups = [
        { role: 'locking', heightCm: d, items: lock, note: 'Locking shot either side of the float' },
        ...evenly(count, lowest, top).map((h) => ({
            role: 'strung',
            heightCm: h,
            items: [{ shot, count: 1 }],
        })),
    ];

    return finish(
        {
            id: 'waggler_drop',
            name: 'On the drop',
            summary: `Locking shot plus ${count} × ${shot.label} strung through the bottom half`,
            whenToUse: 'Shallow swims, or when fish rise to loose feed – the bait falls slowly and naturally.',
            groups,
            tips: [
                'Watch the tip settle after each cast – a missed "settle" is a bite.',
                'Loose-feed regularly so fish compete for falling bait.',
            ],
        },
        input,
    );
}

/** Sliders start at the 4AAA floats; lighter shot cannot pull line through the adaptor. */
const SLIDER_MIN_GRAMS = 4 * SHOT.AAA.grams;

/** Roughly the length of a float rod. Deeper than this and the float has to slide. */
const SLIDER_MIN_DEPTH_CM = 365;

function slider(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const dropper = wagglerDropper(fg);
    let n = d < 450 ? 2 : 3;
    // The bulk has to stay heavy enough to draw line through the float adaptor.
    while (n > 1 && n * dropper.grams > fg * 0.2) n--;

    const bulkTarget = fg - n * dropper.grams;
    const bulk = fillWeight(bulkTarget, shotsBetween(dropper.grams, SHOT.SSG.grams));
    const trim = trimItems(bulkTarget - totalGrams(bulk), dropper.grams * 0.99);

    const lowest = lowestShot(d);
    const bulkH = onLine(Math.max(lowest + 20, clamp(d * 0.2, 60, 120)), d);
    const groups = [
        {
            role: 'bulk',
            heightCm: bulkH,
            items: [...bulk, ...trim],
            note: 'All of the shot goes below the float, which slides on the line',
        },
    ];
    for (let i = 0; i < n; i++) {
        groups.push({
            role: 'dropper',
            heightCm: lowest + (i * (bulkH - lowest)) / n,
            items: [{ shot: dropper, count: 1 }],
        });
    }

    const tips = [
        'Thread the line through a swivel float adaptor so the float slides cleanly and the line does not twist.',
        'Tie the stop knot above the float and leave about an inch of tag, so the float cannot slip over it.',
        'Cast with the bail arm open and let line run until the float settles, then close it and take up the slack.',
        'Plumb up carefully, then slide the stop knot until the float sits with just the tip showing.',
    ];
    if (fg < SLIDER_MIN_GRAMS) {
        tips.unshift('A slider needs a big float – 4AAA (3.20g) and up – so the bulk can pull line through the adaptor.');
    }
    if (d < SLIDER_MIN_DEPTH_CM) {
        tips.unshift('A fixed waggler is easier at this depth; the slider earns its keep once the water is deeper than the rod is long.');
    }

    return finish(
        {
            id: 'slider',
            name: 'Slider',
            summary: `Bulk below the float with ${n} × ${dropper.label} droppers, depth set by a stop knot above the float`,
            whenToUse:
                'Water deeper than your rod is long – reservoirs, gravel pits and tidal rivers where a fixed waggler leaves too long a hooklength to cast.',
            groups,
            tips,
        },
        input,
    );
}

function pelletWaggler(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const lock = fillWeight(fg, shotsBetween(SHOT.No13.grams, Math.max(SHOT.No13.grams, fg / 2)));

    return finish(
        {
            id: 'pellet_waggler',
            name: 'Pellet waggler',
            summary: 'All weight locked at the float – no shot down the line',
            whenToUse: 'Carp and F1s competing for pellets in the upper layers, typically 1–3ft deep.',
            groups: [
                {
                    role: 'locking',
                    heightCm: d,
                    items: lock,
                    note: 'Many pellet wagglers are pre-loaded – only add what is needed to set the tip',
                },
            ],
            tips: [
                'Keep the hooklength short (30–45cm) so the bait falls slowly with the feed.',
                'Feed, cast onto the feed, and expect bites within seconds.',
                'Fish shallower than you think – 2ft is a good starting depth.',
            ],
        },
        input,
    );
}

function recommendedId(input) {
    const { floatGrams: fg, floatType, depthCm: d } = input;
    switch (floatType) {
        case 'pole':
        case 'dibber':
            if (d >= 180 && fg >= 0.75) return 'olivette';
            if (d >= 120 || fg >= 0.5) return 'bulk_droppers';

            return 'strung';
        case 'stick':
            return d < 150 ? 'shirt_button' : 'bulk_droppers';
        case 'avon':
            return 'bulk_droppers';
        case 'waggler':
            if (d >= SLIDER_MIN_DEPTH_CM && fg >= SLIDER_MIN_GRAMS) return 'slider';

            return d < 120 ? 'waggler_drop' : 'waggler_locking';
        case 'slider':
            return 'slider';
        case 'pellet_waggler':
            return 'pellet_waggler';
    }
}

export function generatePatterns(input) {
    const { floatGrams: fg, floatType, depthCm: d } = input;
    const patterns = [];
    switch (floatType) {
        case 'pole':
        case 'dibber':
            if (fg <= 1.2) patterns.push(strung(input));
            patterns.push(bulkDroppers(input));
            patterns.push(olivette(input));
            break;
        case 'stick':
            patterns.push(shirtButton(input), bulkDroppers(input));
            break;
        case 'avon':
            patterns.push(bulkDroppers(input), shirtButton(input));
            break;
        case 'waggler':
            patterns.push(wagglerLocking(input), wagglerDrop(input));
            // Past rod length a fixed waggler cannot be cast, so offer the slider too.
            if (d >= SLIDER_MIN_DEPTH_CM && fg >= SLIDER_MIN_GRAMS) patterns.push(slider(input));
            break;
        case 'slider':
            patterns.push(slider(input));
            break;
        case 'pellet_waggler':
            patterns.push(pelletWaggler(input));
            break;
    }
    const list = patterns.filter((p) => p !== null);
    const rec = recommendedId(input);
    const target = list.find((p) => p.id === rec) ?? list[0];
    if (target) target.recommended = true;

    return list.sort((a, b) => Number(b.recommended) - Number(a.recommended));
}

export const GENERAL_TIPS = [
    'Plumb the depth carefully – set the float so the bait just touches bottom, then adjust.',
    'Shot weights vary between brands. Check the pattern in a tub of water or float tube before you fish.',
    'Use the smallest shot to dot the tip down so only the coloured tip shows.',
];

export const PATTERN_IDS = [
    'strung',
    'bulk_droppers',
    'olivette',
    'shirt_button',
    'waggler_locking',
    'waggler_drop',
    'slider',
    'pellet_waggler',
];
