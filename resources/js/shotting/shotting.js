import { OLIVETTES, SHOT, describeItems, fillWeight, shotsBetween, totalGrams } from './shots';

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

function olivetteHeight(lowest, depthCm) {
    let olH = onLine(clamp(depthCm * 0.3, 40, 100), depthCm);
    if (olH <= lowest + 5) olH = onLine(lowest + 15, depthCm);

    return olH;
}

function sliderBulkHeight(lowest, depthCm) {
    return onLine(Math.max(lowest + 20, clamp(depthCm * 0.2, 60, 120)), depthCm);
}

/**
 * The weight an olivette does not take goes down the line as droppers.
 * A trim smaller than one more dropper may sit with the olivette. A whole shot must not:
 * that is how an 8g olivette on a 9g float grew a second bulk of BB, No.5 and No.12.
 */
function olivetteDroppers(rem, preferred, maxDroppers) {
    const none = { shot: preferred, count: 0, extra: [] };
    if (rem < SHOT.No12.grams) return none;

    const ladder = [SHOT.No12, SHOT.No11, SHOT.No10, SHOT.No9, SHOT.No8, SHOT.No6, SHOT.No5, SHOT.No4].filter(
        (shot) => shot.grams + 1e-9 >= preferred.grams,
    );
    const fits = [];
    for (const shot of ladder) {
        const count = Math.min(maxDroppers, Math.floor((rem + 1e-6) / shot.grams));
        if (count < 1) continue;
        fits.push({ shot, count, leftover: rem - count * shot.grams });
    }

    // The usual dropper size, when another one of them would not fit. If the cap
    // stopped us short, step up a size so the gram is still droppers.
    const finishes = (fit) =>
        fit.leftover < SHOT.No12.grams - 1e-9 || (fit.count < maxDroppers && fit.leftover < fit.shot.grams - 1e-6);
    const natural = fits.find((fit) => fit.shot.size === preferred.size && finishes(fit));
    const chosen =
        natural ??
        fits
            .filter(finishes)
            .sort((a, b) => a.leftover - b.leftover || a.shot.grams - b.shot.grams || a.count - b.count)[0];

    if (chosen) {
        return {
            shot: chosen.shot,
            count: chosen.count,
            extra: trimItems(Math.max(0, chosen.leftover), chosen.shot.grams * 0.99),
        };
    }

    const shot = ladder[ladder.length - 1] ?? preferred;
    const count = Math.min(maxDroppers, Math.floor((rem + 1e-6) / shot.grams));
    const leftover = Math.max(0, rem - count * shot.grams);

    return {
        shot,
        count,
        extra:
            leftover > shot.grams - 1e-6
                ? fillWeight(leftover, shotsBetween(SHOT.No13.grams, Math.max(SHOT.No13.grams, leftover)))
                : trimItems(leftover, shot.grams * 0.99),
    };
}

function olivettePattern(input, spec) {
    const { floatGrams: fg, depthCm: d } = input;
    let options = olivetteOptions(fg);
    if (!options.length) {
        if (spec.requireOptions !== false) return null;
        // Sliders still offer an olivette even on a light float: use the smallest that fits.
        options = OLIVETTES.filter((o) => o < fg);
        if (!options.length) options = [OLIVETTES[0]];
    }

    const base = spec.dropper ?? (poleDropper(fg).grams < SHOT.No10.grams ? SHOT.No10 : poleDropper(fg));
    const maxDroppers = spec.maxDroppers ?? 6;
    // About 80–90% of the float, so the rest is droppers rather than another bulk of shot.
    const inBand = options.filter((o) => o >= fg * 0.8 - 1e-6 && o <= fg * 0.9 + 1e-6);
    const auto =
        inBand[inBand.length - 1] ??
        [...options].reverse().find((o) => o <= fg - Math.min(3, maxDroppers) * base.grams + 1e-6) ??
        options[0];
    const olive = input.olivetteGrams && options.includes(input.olivetteGrams) ? input.olivetteGrams : auto;

    const rem = Math.max(0, fg - olive);
    const { shot: dropper, count: n, extra } = olivetteDroppers(rem, base, maxDroppers);

    const lowest = lowestShot(d);
    const olH = spec.height(lowest, d);

    const groups = [
        {
            role: 'olivette',
            heightCm: olH,
            olivetteGrams: olive,
            items: extra,
            note: extra.length ? 'Olivette, with extra shot just beneath it' : spec.note ?? 'Olivette',
        },
    ];
    for (let i = 0; i < n; i++) {
        groups.push({
            role: 'dropper',
            heightCm: lowest + (i * (olH - lowest)) / Math.max(n, 1),
            items: [{ shot: dropper, count: 1 }],
        });
    }

    const dropperSummary = n ? ` with ${n} × ${dropper.label} droppers` : '';

    return finish(
        {
            id: spec.id,
            name: spec.name,
            summary: `${olive}g olivette${dropperSummary}`,
            whenToUse: spec.whenToUse,
            groups,
            tips: spec.tips,
        },
        input,
    );
}

function olivette(input) {
    return olivettePattern(input, {
        id: 'olivette',
        name: 'Olivette',
        whenToUse:
            'Deep water (6ft+) or heavier pole floats, especially in wind or tow – the most stable pole rig.',
        height: olivetteHeight,
        tips: [
            'Lock the olivette with a small shot or silicone stop either side.',
            'Use an in-line olivette threaded on the main line, not on the hooklength.',
            'Lay the rig in so it falls in a straight line from float to hook.',
        ],
    });
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

/** Shot an angler actually locks a waggler with. No.5 is a stick-float size, not a locking shot. */
const LOCKING_SIZES = ['SSG', 'AAA', 'BB', 'No1', 'No4', 'No6'];

/**
 * About four fifths of an unloaded waggler sits at the float (Total Fishing).
 * Locking shot is always paired, so the same shot sits above and below the float.
 * One piece stays under a third of the float, so 2 × BB cannot cock a 1g waggler on its own.
 */
function lockingItems(floatGrams, lockTarget) {
    const ceiling = Math.max(SHOT.No6.grams, floatGrams / 3);
    const allowed = shotsBetween(SHOT.No6.grams, ceiling).filter((shot) => LOCKING_SIZES.includes(shot.size));
    const sizes = allowed.length ? allowed : [SHOT.No6];

    const out = [];
    let remaining = lockTarget;
    for (const shot of sizes) {
        const pairs = Math.floor((remaining + 1e-6) / (shot.grams * 2));
        if (pairs > 0) {
            out.push({ shot, count: pairs * 2 });
            remaining -= pairs * 2 * shot.grams;
        }
    }

    return out;
}

function wagglerLocking(input) {
    const { floatGrams: fg, depthCm: d } = input;
    const dropper = SHOT.No8;
    let n = d < 120 ? 2 : 3;
    while (n > 1 && n * dropper.grams > fg * 0.25) n--;
    const lock = lockingItems(fg, fg - n * dropper.grams);
    // Weight too small for another locking pair goes down the line, not on as a single shot.
    let leftover = fg - totalGrams(lock) - n * dropper.grams;
    while (leftover >= dropper.grams - 1e-6 && n < 6) {
        n++;
        leftover -= dropper.grams;
    }

    const lowest = lowestShot(d);
    const top = onLine(Math.max(lowest + 10, d * 0.33), d);
    const groups = [
        ...(lock.length
            ? [{
                role: 'locking',
                heightCm: d,
                items: lock,
                note: 'Paired either side of the float – the same shot above and below',
            }]
            : []),
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
                'Lock with AAA, BB, No.4 or No.6, always in pairs, so the same shot sits above and below the float. About four fifths of the float sits at the base, the rest is droppers.',
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
    const spare = fillWeight(
        Math.max(0, lockTarget - totalGrams(lock)),
        shotsBetween(SHOT.No13.grams, Math.max(SHOT.No13.grams, shot.grams)),
    );
    const strungShots = [
        ...Array.from({ length: count }, () => shot),
        ...spare.flatMap((item) => Array.from({ length: item.count }, () => item.shot)),
    ];

    const strungItems = [];
    for (const piece of strungShots) {
        const found = strungItems.find((item) => item.shot.size === piece.size);
        if (found) found.count++;
        else strungItems.push({ shot: piece, count: 1 });
    }

    const lowest = lowestShot(d);
    const top = onLine(Math.max(lowest + 10, d * 0.6), d);
    const groups = [
        ...(lock.length
            ? [{ role: 'locking', heightCm: d, items: lock, note: 'Paired either side of the float – the same shot above and below' }]
            : []),
        ...evenly(strungShots.length, lowest, top).map((h, i) => ({
            role: 'strung',
            heightCm: h,
            items: [{ shot: strungShots[i], count: 1 }],
        })),
    ];

    return finish(
        {
            id: 'waggler_drop',
            name: 'On the drop',
            summary: `Locking shot plus ${describeItems(strungItems)} strung through the bottom half`,
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

function sliderTips(input, extras = []) {
    const { floatGrams: fg, depthCm: d } = input;
    const tips = [
        ...extras,
        'Thread the line through a swivel float adaptor so the float slides cleanly and the line does not twist.',
        'Tie the stop knot above the float and leave about an inch of tag, so the float cannot slip over it.',
        'Cast with the bail arm open and let line run until the float settles, then close it and take up the slack.',
        'Plumb up carefully, then slide the stop knot until the float sits with just the tip showing.',
    ];
    if (fg < SLIDER_MIN_GRAMS) {
        tips.unshift('A slider needs a big float – 4AAA (3.20g) and up – so the weight below can pull line through the adaptor.');
    }
    if (d < SLIDER_MIN_DEPTH_CM) {
        tips.unshift('A fixed waggler is easier at this depth; the slider earns its keep once the water is deeper than the rod is long.');
    }

    return tips;
}

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
    const bulkH = sliderBulkHeight(lowest, d);
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

    return finish(
        {
            id: 'slider',
            name: 'Bulk',
            summary: `Bulk below the float with ${n} × ${dropper.label} droppers, depth set by a stop knot above the float`,
            whenToUse:
                'Water deeper than your rod is long – reservoirs, gravel pits and tidal rivers where a fixed waggler leaves too long a hooklength to cast.',
            groups,
            tips: sliderTips(input),
        },
        input,
    );
}

function sliderOlivette(input) {
    return olivettePattern(input, {
        id: 'slider_olivette',
        name: 'Olivette',
        requireOptions: false,
        dropper: wagglerDropper(input.floatGrams),
        maxDroppers: 6,
        note: 'Olivette below the float, which slides on the line',
        whenToUse:
            'The same deep-water slider, with an olivette instead of a bulk of shot – cleaner through the water and more stable in wind or tow.',
        height: sliderBulkHeight,
        tips: sliderTips(input, [
            'Lock the olivette with a small shot or silicone stop either side.',
            'Use an in-line olivette threaded on the main line, not on the hooklength.',
        ]),
    });
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

function neatBulkShot(grams) {
    for (const shot of [SHOT.BB, SHOT.AAA, SHOT.No4, SHOT.SSG, SHOT.No1, SHOT.No6]) {
        const count = grams / shot.grams;
        if (count >= 1 && Math.abs(count - Math.round(count)) < 0.05) return shot;
    }

    return SHOT.No4;
}

/** The printed shot, made up exactly. A remainder smaller than that shot is added as smaller shot. */
function bulkForShot(target, shot) {
    const count = Math.floor((target + 1e-6) / shot.grams);
    const items = count > 0 ? [{ shot, count }] : [];
    const leftover = target - count * shot.grams;

    return [...items, ...trimItems(leftover, shot.grams * 0.99)];
}

function loadedAllAtFloat(input, shot) {
    const { floatGrams: fg, depthCm: d } = input;
    const items = bulkForShot(fg, shot);
    const text = describeItems(items);

    return finish(
        {
            id: `loaded_${shot.size.toLowerCase()}`,
            name: items.length === 1 ? text : shot.label,
            summary: `${text} just under the float, held with float stops`,
            whenToUse:
                'All of the shot the float asks for, bunched at the adaptor, when you want the bait down in one go.',
            groups: [
                {
                    role: 'stops',
                    heightCm: d,
                    items: [],
                    note: 'Silicone stops set the depth — do not lock a loaded waggler with shot',
                },
                {
                    role: 'bulk',
                    heightCm: onLine(d - 15, d),
                    items,
                    note: 'The whole of the added shot, around the adaptor',
                },
            ],
            tips: [
                'Fix the float with silicone stops either side of the adaptor. The shot does not lock it in place.',
                'This uses the whole of the shot printed after the plus, with nothing down the line.',
                'Swap to Bulk & droppers if you want the last few inches to fall slowly.',
            ],
        },
        input,
    );
}

function loadedWithDroppers(input, printed) {
    const { floatGrams: fg, depthCm: d } = input;
    const dropper = SHOT.No8;
    let n = d < 120 ? 2 : 3;
    while (n > 1 && n * dropper.grams > fg * 0.35) n--;

    const bulk = bulkForShot(fg - n * dropper.grams, printed);
    const lowest = lowestShot(d);
    const bulkH = onLine(Math.max(lowest + 15, d - 15), d);
    const groups = [
        {
            role: 'stops',
            heightCm: d,
            items: [],
            note: 'Silicone stops set the depth — do not lock a loaded waggler with shot',
        },
        {
            role: 'bulk',
            heightCm: bulkH,
            items: bulk,
            note: 'Most of the added shot, around the adaptor',
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
            id: 'loaded_waggler',
            name: 'Bulk & droppers',
            summary: `${describeItems(bulk)} under the float, ${n} × ${dropper.label} droppers`,
            whenToUse:
                'A loaded waggler already carries weight in the base. Stops set the depth, the bulk cocks the float, and the droppers show the bite.',
            groups,
            tips: [
                'Fix the float with silicone stops either side of the adaptor. The shot does not lock it in place.',
                'The 2 × BB on a 1+2BB is the shot you add in total. Most of it sits at the adaptor; a few No.8s go down the line.',
                'The other chips put that same weight on as 2 × BB, No.4 or No.6, with nothing down the line.',
                'The weight already inside the float is not shot you add on the line.',
            ],
        },
        input,
    );
}

function loadedPatterns(input) {
    const printed = input.addShot && SHOT[input.addShot] ? SHOT[input.addShot] : neatBulkShot(input.floatGrams);
    const choices = [];
    for (const shot of [printed, SHOT.No4, SHOT.No6, SHOT.BB]) {
        if (shot.grams < input.floatGrams - 1e-6 && ! choices.some((choice) => choice.size === shot.size)) {
            choices.push(shot);
        }
    }

    return [loadedWithDroppers(input, printed), ...choices.map((shot) => loadedAllAtFloat(input, shot))];
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
        case 'loaded_waggler':
            return 'loaded_waggler';
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
            if (d >= SLIDER_MIN_DEPTH_CM && fg >= SLIDER_MIN_GRAMS) {
                patterns.push(slider(input), sliderOlivette(input));
            }
            break;
        case 'slider':
            patterns.push(slider(input), sliderOlivette(input));
            break;
        case 'loaded_waggler':
            patterns.push(...loadedPatterns(input));
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
    'slider_olivette',
    'loaded_waggler',
    'loaded_bb',
    'loaded_no4',
    'loaded_no6',
    'loaded_aaa',
    'pellet_waggler',
];

export function patternUsesOlivette(id) {
    return id === 'olivette' || id === 'slider_olivette';
}
