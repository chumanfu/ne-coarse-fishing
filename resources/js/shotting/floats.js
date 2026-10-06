import { SHOT } from './shots';

export const FLOAT_TYPES = [
    { id: 'pole', label: 'Pole', hint: 'Pole floats rated like 4x10, 4x16 or in grams' },
    { id: 'dibber', label: 'Dibber', hint: 'Short margin/shallow pole floats' },
    { id: 'waggler', label: 'Waggler', hint: 'Straight, insert or bodied wagglers' },
    { id: 'pellet_waggler', label: 'Pellet wag', hint: 'Short, often pre-loaded wagglers' },
    { id: 'stick', label: 'Stick', hint: 'Running-water stick floats, e.g. 4 No.4' },
    { id: 'avon', label: 'Avon', hint: 'Bodied running-water floats, e.g. 3AAA' },
];

export function floatTypeLabel(t) {
    return FLOAT_TYPES.find((f) => f.id === t)?.label ?? t;
}

const SHOT_TOKENS = {
    ssg: 'SSG',
    aaa: 'AAA',
    ab: 'AB',
    bb: 'BB',
};

function round(n, dp = 2) {
    const f = Math.pow(10, dp);

    return Math.round(n * f) / f;
}

export function formatGrams(g) {
    if (g >= 10) return `${round(g, 1)}g`;

    return `${round(g, 2).toFixed(2)}g`;
}

/**
 * Parses the size written on a float. Supports:
 *  - pole notation: "4x10", "4X16", "4 x 0.2"  (4x10 ≈ 0.10g)
 *  - grams: "0.5g", "1.5 grams", "0.4"
 *  - shot ratings: "3BB", "2.5AAA", "4No4", "6 x No.8", "2SSG"
 */
export function parseFloatSize(input) {
    const s = input.trim().toLowerCase().replace(/\s+/g, '').replace(/×/g, 'x');
    if (!s) return null;

    // Shot rating: optional count, optional "x", then shot name.
    const shotMatch = s.match(/^(\d+(?:\.\d+)?)?x?(ssg|aaa|ab|bb|no\.?(\d{1,2})|#(\d{1,2}))$/);
    if (shotMatch) {
        const count = shotMatch[1] ? parseFloat(shotMatch[1]) : 1;
        let size = SHOT_TOKENS[shotMatch[2]];
        const num = shotMatch[3] ?? shotMatch[4];
        if (!size && num) {
            const key = `No${parseInt(num, 10)}`;
            size = SHOT[key] ? key : undefined;
        }
        if (!size || count <= 0) return null;
        const grams = count * SHOT[size].grams;

        return withRange({
            grams,
            notation: 'shot',
            shotSize: size,
            explanation: `${count} × ${SHOT[size].label} ≈ ${formatGrams(grams)}`,
        });
    }

    // Pole notation "4x10" → 0.10g. A decimal second number ("4x0.2") is read as grams.
    const poleMatch = s.match(/^(\d+)x(\d+(?:\.\d+)?)$/);
    if (poleMatch) {
        const second = poleMatch[2];
        const grams = second.includes('.') ? parseFloat(second) : parseInt(second, 10) / 100;

        return withRange({
            grams,
            notation: 'pole',
            explanation: `${poleMatch[1]}x${second} pole rating ≈ ${formatGrams(grams)}`,
        });
    }

    const gramMatch = s.match(/^(\d*\.?\d+)(g|gr|gram|grams)?$/);
    if (gramMatch) {
        const grams = parseFloat(gramMatch[1]);

        return withRange({ grams, notation: 'grams', explanation: `${formatGrams(grams)} float` });
    }

    return null;
}

function withRange(p) {
    if (!isFinite(p.grams) || p.grams < 0.02 || p.grams > 30) return null;

    return p;
}

/** Best guess at the float type from how its size is written. */
export function guessFloatType(p) {
    if (p.notation === 'pole') return 'pole';
    if (p.shotSize && p.shotSize.startsWith('No')) return 'stick';
    if (p.notation === 'shot') return 'waggler';
    if (p.grams < 1.2) return 'pole';

    return 'waggler';
}
