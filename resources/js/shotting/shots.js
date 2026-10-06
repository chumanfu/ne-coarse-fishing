// Approximate split-shot weights in grams (UK "Style" sizes). Brands vary by
// a few percent, so the app always presents totals as approximate.
import reference from '../../data/shot-reference.json';

/** Ordered heaviest to lightest. */
export const SHOTS = reference.shots;

export const SHOT = SHOTS.reduce((acc, s) => {
    acc[s.size] = s;

    return acc;
}, {});

/** Shots whose weight lies within [minGrams, maxGrams], heaviest first. */
export function shotsBetween(minGrams, maxGrams) {
    return SHOTS.filter((s) => s.grams >= minGrams - 1e-9 && s.grams <= maxGrams + 1e-9);
}

/**
 * Greedily make up `target` grams from the allowed sizes without exceeding it.
 * The leftover is always smaller than the lightest allowed shot.
 */
export function fillWeight(target, allowed) {
    const out = [];
    let remaining = target;
    for (const shot of allowed) {
        const count = Math.floor((remaining + 1e-6) / shot.grams);
        if (count > 0) {
            out.push({ shot, count });
            remaining -= count * shot.grams;
        }
    }

    return out;
}

export function totalGrams(items) {
    return items.reduce((sum, i) => sum + i.count * i.shot.grams, 0);
}

export function describeItems(items) {
    return items.map((i) => `${i.count} × ${i.shot.label}`).join(' + ');
}

/** Olivette sizes commonly sold, in grams. */
export const OLIVETTES = reference.olivettes;
