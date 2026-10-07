import { SHOT } from './shots';

export const FLOAT_TYPES = [
    { id: 'pole', label: 'Pole', hint: 'Pole floats rated like 4x10, 4x16 or in grams' },
    { id: 'dibber', label: 'Dibber', hint: 'Short margin/shallow pole floats' },
    { id: 'waggler', label: 'Waggler', hint: 'Straight, insert or bodied wagglers, locked with shot' },
    { id: 'loaded_waggler', label: 'Loaded', hint: 'Weight already in the base — held with float stops, not locking shot' },
    { id: 'pellet_waggler', label: 'Pellet wag', hint: 'Short, often pre-loaded wagglers' },
    { id: 'slider', label: 'Slider', hint: 'Big waggler (4AAA+) that slides on the line for deep water' },
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

const SHOT_NAME = String.raw`ssg|aaa|ab|bb|no\.?\d{1,2}|#\d{1,2}`;
const GRAM_UNIT = String.raw`g|gr|gram|grams`;

function round(n, dp = 2) {
    const f = Math.pow(10, dp);

    return Math.round(n * f) / f;
}

export function formatGrams(g) {
    if (g >= 10) return `${round(g, 1)}g`;

    return `${round(g, 2).toFixed(2)}g`;
}

function resolveShot(token) {
    const match = String(token).match(/^(ssg|aaa|ab|bb|no\.?(\d{1,2})|#(\d{1,2}))$/);
    if (!match) return null;
    if (SHOT_TOKENS[match[1]]) return SHOT_TOKENS[match[1]];
    const num = match[2] ?? match[3];
    const key = `No${parseInt(num, 10)}`;

    return SHOT[key] ? key : null;
}

function loadedResult(loadedGrams, addGrams, explanation, addShot = null, addCount = null) {
    return withRange({
        grams: addGrams,
        loadedGrams,
        notation: 'loaded',
        addShot,
        addCount,
        explanation,
    });
}

/**
 * Parses the size written on a float. Supports:
 *  - pole notation: "4x10", "4X16", "4 x 0.2"  (4x10 ≈ 0.10g)
 *  - grams: "0.5g", "1.5 grams", "0.4"
 *  - shot ratings: "3BB", "2.5AAA", "4No4", "6 x No.8", "2SSG"
 *  - loaded wagglers: "1+2BB", "1BB+2BB", "0.4+0.8gr", "1.5g + 0.5g", "1+2BB 0.4+0.8 gr"
 *    (loading already in the float + shot to add on the line)
 */
export function parseFloatSize(input) {
    const s = input.trim().toLowerCase().replace(/,/g, '.').replace(/\s+/g, '').replace(/×/g, 'x');
    if (!s) return null;

    // Printed on loaded crystals: "1+2BB 0.4+0.8 gr" — grams are the source of truth.
    const loadedBoth = s.match(
        new RegExp(
            `^(\\d+(?:\\.\\d+)?)[+x](\\d+(?:\\.\\d+)?)(${SHOT_NAME})(\\d+(?:\\.\\d+)?)[+x](\\d+(?:\\.\\d+)?)(?:${GRAM_UNIT})?$`,
        ),
    );
    if (loadedBoth) {
        const loadedGrams = parseFloat(loadedBoth[4]);
        const addGrams = parseFloat(loadedBoth[5]);
        const shot = resolveShot(loadedBoth[3]);
        const shotNote = shot ? ` (${loadedBoth[1]}+${loadedBoth[2]} ${SHOT[shot].label})` : '';

        return loadedResult(
            loadedGrams,
            addGrams,
            `Loaded ${formatGrams(loadedGrams)} in the float; add ${formatGrams(addGrams)} of shot${shotNote}`,
            shot,
            shot ? parseFloat(loadedBoth[2]) : null,
        );
    }

    // "1BB+2BB" or "1ssg+2aaa"
    const loadedNamed = s.match(
        new RegExp(`^(\\d+(?:\\.\\d+)?)(${SHOT_NAME})[+x](\\d+(?:\\.\\d+)?)(${SHOT_NAME})$`),
    );
    if (loadedNamed) {
        const loadedSize = resolveShot(loadedNamed[2]);
        const addSize = resolveShot(loadedNamed[4]);
        if (loadedSize && addSize) {
            const loadedGrams = parseFloat(loadedNamed[1]) * SHOT[loadedSize].grams;
            const addGrams = parseFloat(loadedNamed[3]) * SHOT[addSize].grams;

            return loadedResult(
                loadedGrams,
                addGrams,
                `Loaded ${loadedNamed[1]} × ${SHOT[loadedSize].label} (${formatGrams(loadedGrams)}) in the float; add ${loadedNamed[3]} × ${SHOT[addSize].label} (${formatGrams(addGrams)})`,
                addSize,
                parseFloat(loadedNamed[3]),
            );
        }
    }

    // "1+2BB" — both counts are the same shot size.
    const loadedShot = s.match(new RegExp(`^(\\d+(?:\\.\\d+)?)[+x](\\d+(?:\\.\\d+)?)(${SHOT_NAME})$`));
    if (loadedShot) {
        const size = resolveShot(loadedShot[3]);
        if (size) {
            const loadedGrams = parseFloat(loadedShot[1]) * SHOT[size].grams;
            const addGrams = parseFloat(loadedShot[2]) * SHOT[size].grams;

            return loadedResult(
                loadedGrams,
                addGrams,
                `Loaded ${loadedShot[1]} × ${SHOT[size].label} (${formatGrams(loadedGrams)}) in the float; add ${loadedShot[2]} × ${SHOT[size].label} (${formatGrams(addGrams)})`,
                size,
                parseFloat(loadedShot[2]),
            );
        }
    }

    // "0.4+0.8gr", "1.5g+0.5g", "1.5g + 0.5g" — a unit may sit on either number.
    const loadedGramsOnly = s.match(
        new RegExp(`^(\\d+(?:\\.\\d+)?)(?:${GRAM_UNIT})?[+x](\\d+(?:\\.\\d+)?)(?:${GRAM_UNIT})?$`),
    );
    if (loadedGramsOnly && new RegExp(GRAM_UNIT).test(s)) {
        const loadedGrams = parseFloat(loadedGramsOnly[1]);
        const addGrams = parseFloat(loadedGramsOnly[2]);

        return loadedResult(
            loadedGrams,
            addGrams,
            `Loaded ${formatGrams(loadedGrams)} in the float; add ${formatGrams(addGrams)} of shot`,
        );
    }

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

    const gramMatch = s.match(new RegExp(`^(\\d*\\.?\\d+)(?:${GRAM_UNIT})?$`));
    if (gramMatch) {
        const grams = parseFloat(gramMatch[1]);

        return withRange({ grams, notation: 'grams', explanation: `${formatGrams(grams)} float` });
    }

    return null;
}

function withRange(p) {
    if (!isFinite(p.grams) || p.grams < 0.02 || p.grams > 30) return null;
    if (p.loadedGrams != null && (!isFinite(p.loadedGrams) || p.loadedGrams < 0)) return null;

    return p;
}

/** Best guess at the float type from how its size is written. */
export function guessFloatType(p) {
    if (p.notation === 'loaded') return 'loaded_waggler';
    if (p.notation === 'pole') return 'pole';
    if (p.shotSize && p.shotSize.startsWith('No')) return 'stick';
    if (p.notation === 'shot') return 'waggler';
    if (p.grams < 1.2) return 'pole';

    return 'waggler';
}
