import { FLOAT_TYPES, floatTypeLabel, formatGrams, guessFloatType, parseFloatSize } from './floats';
import { SHOT, SHOTS, describeItems } from './shots';
import { GENERAL_TIPS, MIN_DEPTH_CM, generatePatterns, groupGrams, olivetteOptions, patternUsesOlivette } from './shotting';
import { depthToCm, formatHeight } from './units';

const ROLE_LABEL = {
    locking: 'Locking',
    stops: 'Stops',
    bulk: 'Bulk',
    olivette: 'Olivette',
    dropper: 'Dropper',
    strung: 'Strung',
    trim: 'Trimmer',
    dot: 'Dot',
};

const LINE_X = 48;
const LABEL_X = 96;
const WATER_Y = 48; // the water line the float sinks through
const BRISTLE_H = 20;
const MAX_SINK = BRISTLE_H + 8;
const MAX_RISE = 16;

/**
 * Each float is drawn so the sight tip is the part above the water when the
 * rig is dotted down. `tipH` is that tip; the rest of the picture sits in the water.
 */
const FLOAT_PICTURES = {
    pole: { w: 24, h: 66, tipH: 24 },
    dibber: { w: 33, h: 45, tipH: 14 },
    waggler: { w: 21, h: 78, tipH: 21 },
    loaded_waggler: { w: 24, h: 81, tipH: 21 },
    pellet_waggler: { w: 30, h: 54, tipH: 15 },
    slider: { w: 27, h: 96, tipH: 24 },
    stick: { w: 24, h: 75, tipH: 15 },
    avon: { w: 30, h: 78, tipH: 15 },
};

function floatPicture(type) {
    const picture = FLOAT_PICTURES[type] ?? FLOAT_PICTURES.pole;

    return { ...picture, kind: picture === FLOAT_PICTURES[type] ? type : 'pole' };
}

const LABEL_SPACING = 6;
const LABEL_ESTIMATE = 36; // used until a label has been measured
const LABEL_OVERHANG_TOP = 20; // how far labels may sit above the float
const LABEL_OVERHANG_BOTTOM = 10; // and below the hook

/** Small shot used to dot a tip. Heaviest first, same order as the rest of the rig. */
const DOT_SIZES = ['No6', 'No8', 'No9', 'No10', 'No11', 'No12', 'No13'];

function tipName(floatType) {
    return floatType === 'pole' || floatType === 'dibber' ? 'bristle' : 'coloured tip';
}

/**
 * Ways to make up the grams still needed, or a few single shots to try when the
 * float is already on weight. The angler picks one, then adds or changes a shot.
 */
function dotSuggestions(shortfall, shots) {
    const goal = Math.max(0, shortfall);
    const options = [];
    for (const shot of shots) {
        options.push({ id: shot.size, shots: [shot.size], grams: shot.grams, label: `1 × ${shot.label}` });
        if (goal > 0.015 && shot.grams * 2 <= goal + shot.grams * 0.5 + 1e-6) {
            options.push({
                id: `${shot.size}x2`,
                shots: [shot.size, shot.size],
                grams: shot.grams * 2,
                label: `2 × ${shot.label}`,
            });
        }
    }

    options.sort(
        (a, b) => Math.abs(a.grams - goal) - Math.abs(b.grams - goal) || a.shots.length - b.shots.length || a.grams - b.grams,
    );

    const picked = [];
    for (const option of options) {
        if (picked.some((choice) => choice.label === option.label)) continue;
        if (goal > 0.02 && option.grams > goal + 0.08) continue;
        if (goal <= 0.015 && option.shots.length > 1) continue;
        picked.push(option);
        if (picked.length === 4) break;
    }

        if (goal <= 0.015 && ! picked.some((choice) => choice.id === 'No8')) {
            const no8 = shots.find((shot) => shot.size === 'No8');
            if (no8) {
                if (picked.length >= 4) picked.pop();
                picked.push({ id: 'No8', shots: ['No8'], grams: no8.grams, label: `1 × ${no8.label}` });
            }
        }

        return [{ id: 'none', shots: [], grams: 0, label: 'As shotted' }, ...picked];
}

function tipSit(overGrams, floatType) {
    const tip = tipName(floatType);
    if (overGrams > SHOT.No8.grams * 0.85) {
        return {
            title: 'Tip under',
            detail: `The ${tip} has gone under. Take a shot off, or change to a smaller one.`,
        };
    }
    if (overGrams > 0.012) {
        return {
            title: 'Dipping under',
            detail: `The ${tip} is going under the water.`,
        };
    }
    if (overGrams >= -0.012) {
        return {
            title: tip === 'bristle' ? 'Bristle showing' : 'Coloured tip showing',
            detail: `Only the ${tip} is above the water.`,
        };
    }

    return {
        title: 'Body showing',
        detail: `Add a shot to dot the float down to the ${tip}.`,
    };
}

function groupText(g) {
    if (g.role === 'stops') return 'Float stops';

    const parts = [];
    if (g.olivetteGrams) parts.push(`${g.olivetteGrams}g olivette`);
    if (g.items.length) parts.push(describeItems(g.items));

    return parts.join(' + ');
}

function distanceFields(group, depthUnit, depthCm) {
    const distanceUnit = group.distanceUnit ?? (depthUnit === 'ft' ? 'in' : 'cm');
    const anchor = group.anchor ?? 'hook';
    const cm = anchor === 'float' ? depthCm - group.heightCm : group.heightCm;
    const distance = distanceUnit === 'in' ? cm / 2.54 : cm;

    return {
        distance: Math.round(Math.max(0, distance) * 10) / 10,
        distanceUnit,
        anchor,
    };
}

function positionText(g, depthUnit, depthCm) {
    if (g.role === 'locking' || g.role === 'stops') return 'At the float';
    if (g.role === 'dot') return 'Under the float';

    const { distance, distanceUnit, anchor } = distanceFields(g, depthUnit, depthCm);
    const from = anchor === 'float' ? 'float' : 'hook';

    return `${distance}${distanceUnit} from the ${from}`;
}

/** Diameter of one shot on the diagram, from that shot's own weight. */
function shotMarkSize(grams) {
    return Math.max(7, Math.min(22, 6 + Math.sqrt(Math.max(0, grams)) * 14));
}

/**
 * One mark per shot in the placement, stacked on the line and centred on its
 * distance from the hook. An olivette or the float stops stay a single piece.
 */
function placementMarks(group, y) {
    if (group.role === 'stops') {
        return [{
            tone: 'stops',
            style: `width:16px;height:4px;border-radius:2px;top:${y - 2}px;left:${LINE_X - 8}px`,
        }];
    }

    const pieces = [];

    if (group.olivetteGrams) {
        pieces.push({ w: 12, h: 26, radius: 6, tone: 'shot' });
    }

    const tone = group.role === 'locking' ? 'locking' : 'shot';

    for (const item of group.items ?? []) {
        const count = Math.max(0, Math.round(Number(item.count) || 0));
        const size = shotMarkSize(item.shot?.grams ?? 0);

        for (let n = 0; n < count; n++) {
            pieces.push({ w: size, h: size, radius: size / 2, tone });
        }
    }

    if (pieces.length === 0) {
        const size = shotMarkSize(0);
        pieces.push({ w: size, h: size, radius: size / 2, tone });
    }

    const gap = pieces.length > 1 ? 2 : 0;
    const total = pieces.reduce((sum, piece) => sum + piece.h, 0) + gap * (pieces.length - 1);
    let top = y - total / 2;

    return pieces.map((piece) => {
        const style = `width:${piece.w}px;height:${piece.h}px;border-radius:${piece.radius}px;top:${top}px;left:${LINE_X - piece.w / 2}px`;
        top += piece.h + gap;

        return { tone: piece.tone, style };
    });
}

/** A saved rig stores plain shot, already placed on the line. */
function groupsFromSaved(placements) {
    return placements.map((group, index) => ({
        key: `${group.role}-${index}`,
        role: group.role,
        heightCm: Number(group.height_cm),
        olivetteGrams: group.olivette_grams ?? null,
        note: group.note ?? null,
        anchor: group.anchor === 'float' ? 'float' : 'hook',
        distanceUnit: group.distance_unit === 'in' ? 'in' : 'cm',
        items: (group.items ?? []).map((item) => ({ size: item.size, count: Number(item.count) || 1 })),
    }));
}

/** A plain copy of the engine groups, so each shot can be changed without touching the suggestion. */
function cloneGroups(groups) {
    return groups.map((group, index) => ({
        key: `${group.role}-${index}`,
        role: group.role,
        heightCm: group.heightCm,
        olivetteGrams: group.olivetteGrams ?? null,
        note: group.note ?? null,
        items: (group.items ?? []).map((item) => ({ size: item.shot.size, count: item.count })),
    }));
}

function hydrateGroup(group) {
    return {
        ...group,
        items: group.items
            .filter((item) => SHOT[item.size])
            .map((item) => ({ shot: SHOT[item.size], count: item.count })),
    };
}

/**
 * Places labels as close to their shot as possible without overlapping:
 * a top-down pass pushes them apart, then a bottom-up pass pulls them back
 * above `maxBottom` where there is room.
 */
function layoutLabels(centres, heights, minTop, maxBottom) {
    const tops = centres.map((c, i) => c - heights[i] / 2);
    for (let i = 0; i < tops.length; i++) {
        const floor = i === 0 ? minTop : tops[i - 1] + heights[i - 1] + LABEL_SPACING;
        tops[i] = Math.max(tops[i], floor);
    }
    for (let i = tops.length - 1; i >= 0; i--) {
        const ceiling = i === tops.length - 1 ? maxBottom : tops[i + 1] - LABEL_SPACING;
        tops[i] = Math.max(minTop, Math.min(tops[i], ceiling - heights[i]));
    }
    for (let i = 1; i < tops.length; i++) {
        tops[i] = Math.max(tops[i], tops[i - 1] + heights[i - 1] + LABEL_SPACING);
    }

    return tops;
}

export default function shottingCalculator(config = {}) {
    return {
        floatTypes: FLOAT_TYPES,
        generalTips: GENERAL_TIPS,
        lineX: LINE_X,
        labelX: LABEL_X,

        floatName: config.floatName ?? '',
        floatSize: config.floatSize ?? '',
        ratedGrams: config.ratedGrams ?? null,
        floatType: config.floatType ?? 'pole',
        typeChosen: Boolean(config.floatType),
        depthText: config.depth ? String(config.depth) : '',
        unit: config.depthUnit ?? 'ft',
        patternId: config.patternId ?? null,
        olivetteGrams: config.olivetteGrams ?? null,
        dots: [],
        dotSize: 'No8',
        addSize: 'No8',
        dotPick: 'none',
        extraShot: 0,
        savedPlacements: Array.isArray(config.placements) && config.placements.length ? config.placements : null,
        openingPlacements: null,
        edits: Array.isArray(config.placements) && config.placements.length ? groupsFromSaved(config.placements) : null,
        shotSizes: SHOTS,
        shotCounts: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        labelHeights: [],

        venues: config.venues ?? [],
        venueIds: (config.venueIds ?? []).map(String),
        pegIds: (config.pegIds ?? []).map(String),
        rigName: config.rigName ?? '',

        init() {
            this.remeasure = () => this.measureLabels();
            window.addEventListener('resize', this.remeasure);
            this.$watch('floatSize', () => {
                this.ratedGrams = null;
                this.resetLine();
            });
            this.$watch('floatType', () => this.resetLine());
            this.$watch('patternId', () => this.resetLine());
            this.$watch('olivetteGrams', () => this.resetLine());
            this.$watch('depthText', () => this.resetLine());
            this.openingPlacements = this.savedPlacements ? this.placementsPayload : null;
        },

        destroy() {
            window.removeEventListener('resize', this.remeasure);
        },

        get parsed() {
            return parseFloatSize(this.floatSize);
        },

        get depth() {
            return parseFloat(String(this.depthText).replace(',', '.'));
        },

        get depthCm() {
            return isFinite(this.depth) ? depthToCm(this.depth, this.unit) : NaN;
        },

        get depthValid() {
            return isFinite(this.depthCm) && this.depthCm >= MIN_DEPTH_CM && this.depthCm <= 1500;
        },

        get sizeInvalid() {
            return Boolean(this.floatSize) && ! this.parsed;
        },

        get depthInvalid() {
            return Boolean(this.depthText) && ! this.depthValid;
        },

        get sizeHint() {
            if (! this.floatSize) return null;
            if (this.ratedGrams != null) return `Shotted to ${formatGrams(this.ratedGrams)}`;

            return this.parsed
                ? this.parsed.explanation
                : 'Not recognised – try a format like 4x12, 0.3g, 2AAA, 1+2BB or 1.5g + 0.5g';
        },

        get patterns() {
            if (! this.parsed || ! this.depthValid) return [];

            return generatePatterns({
                floatGrams: this.parsed.grams,
                floatType: this.floatType,
                depthCm: this.depthCm,
                olivetteGrams: this.olivetteGrams ?? undefined,
                addShot: this.parsed.addShot ?? undefined,
            });
        },

        get active() {
            const patterns = this.patterns;

            return patterns.find((p) => p.id === this.patternId) ?? patterns[0] ?? null;
        },

        get activeOlivette() {
            return this.active?.groups.find((g) => g.olivetteGrams)?.olivetteGrams ?? null;
        },

        get olivetteChoices() {
            return this.parsed ? olivetteOptions(this.parsed.grams) : [];
        },

        get usesOlivette() {
            return patternUsesOlivette(this.active?.id);
        },

        get loadSummary() {
            const pattern = this.active;
            if (! pattern) return null;
            const capacity = this.ratedGrams ?? pattern.floatGrams;
            const sit = tipSit(this.shotLoadGrams - capacity, this.floatType);

            return {
                heading: `Shot load ${formatGrams(this.shotLoadGrams)} of ${formatGrams(capacity)}`,
                detail: this.parsed?.loadedGrams && this.displayGroups.some((group) => group.role === 'stops')
                    ? `Plus ${formatGrams(this.parsed.loadedGrams)} already in the float. The stops set the depth. ${sit.detail}`
                    : sit.detail,
            };
        },

        get dotSizeOptions() {
            return DOT_SIZES.map((size) => SHOT[size]);
        },

        get dotItems() {
            return DOT_SIZES
                .map((size) => ({ shot: SHOT[size], count: this.dots.filter((dot) => dot === size).length }))
                .filter((item) => item.count > 0);
        },

        get dotGrams() {
            return this.dots.reduce((sum, size) => sum + SHOT[size].grams, 0);
        },

        get dotChoices() {
            const pattern = this.active;
            if (! pattern) return [];

            return dotSuggestions(pattern.floatGrams - pattern.loadGrams, this.dotSizeOptions);
        },

        /** Engine groups, or the angler's edited copy once they have changed a shot. */
        get placements() {
            if (this.edits) return this.edits;
            if (! this.active) return [];

            return cloneGroups(this.active.groups);
        },

        /** Pattern groups plus any shot the angler has added to dot the tip. */
        get displayGroups() {
            const groups = this.placements.map(hydrateGroup);
            if (this.dots.length && this.depthValid) {
                groups.push({
                    role: 'dot',
                    heightCm: Math.max(this.depthCm - 25, this.depthCm * 0.82),
                    items: this.dotItems,
                    note: 'Just under the float, to dot the tip',
                });
            }

            return groups.sort((a, b) => b.heightCm - a.heightCm);
        },

        get shotLoadGrams() {
            return this.displayGroups.reduce((sum, group) => sum + groupGrams(group), 0);
        },

        /** Table rows for the pattern, in the same order as the diagram. */
        get rows() {
            const sorted = this.displayGroups;
            const movable = sorted.filter((group) => group.key && group.role !== 'stops' && group.role !== 'dot');

            return sorted.map((group) => {
                const moveAt = movable.findIndex((item) => item.key === group.key);

                return {
                    key: group.key ?? group.role,
                    role: ROLE_LABEL[group.role] ?? group.role,
                    text: groupText(group),
                    position: positionText(group, this.unit, this.depthCm),
                    note: group.note ?? null,
                    grams: group.role === 'stops' ? '' : formatGrams(groupGrams(group)),
                    editable: Boolean(group.key) && group.role !== 'stops' && group.role !== 'dot',
                    olivetteLabel: group.olivetteGrams ? `${group.olivetteGrams}g olivette` : null,
                    items: (group.items ?? []).map((item, itemIndex) => ({
                        itemIndex,
                        size: item.shot.size,
                        count: item.count,
                    })),
                    canMoveUp: moveAt > 0,
                    canMoveDown: moveAt >= 0 && moveAt < movable.length - 1,
                    canAddShot: group.role === 'bulk' || group.role === 'olivette',
                    canPlace: Boolean(group.key) && ! ['stops', 'locking', 'dot'].includes(group.role),
                    ...distanceFields(group, this.unit, this.depthCm),
                };
            });
        },

        get diagram() {
            const pattern = this.active;
            if (! pattern || ! this.depthValid) return null;

            const depthCm = this.depthCm;
            const source = this.displayGroups;
            const heights = source.map((_, i) => this.labelHeights[i] ?? LABEL_ESTIMATE);
            // The line stretches to fit the labels, so a pattern with more shot than
            // the depth leaves room for never ends up with the hook off the bottom.
            const labelsNeeded = heights.reduce((sum, h) => sum + h + LABEL_SPACING, -LABEL_SPACING);
            const picture = floatPicture(this.floatType);
            const lineTop = WATER_Y + MAX_SINK + (picture.h - picture.tipH) + 10;
            const lineLen = Math.max(
                Math.min(420, Math.max(220, depthCm * 1.1)),
                labelsNeeded - LABEL_OVERHANG_TOP - LABEL_OVERHANG_BOTTOM,
            );
            const hookY = lineTop + lineLen;
            const yFor = (h) => lineTop + (1 - h / depthCm) * lineLen;

            const groups = source.map((g) => ({ g, y: yFor(g.heightCm), text: groupText(g) }));
            const tops = layoutLabels(
                groups.map(({ y }) => y),
                heights,
                lineTop - LABEL_OVERHANG_TOP,
                hookY + LABEL_OVERHANG_BOTTOM,
            );
            const labelsBottom = tops.length ? tops[tops.length - 1] + heights[tops.length - 1] : 0;
            const bedY = Math.max(hookY + 14, labelsBottom + 8);
            const capacity = this.ratedGrams ?? pattern.floatGrams;
            const over = this.shotLoadGrams - capacity;
            const sinkPx = over >= 0
                ? Math.min(MAX_SINK, (over / SHOT.No8.grams) * BRISTLE_H)
                : -Math.min(MAX_RISE, (-over / Math.max(SHOT.No4.grams, capacity * 0.12)) * MAX_RISE);
            const floatTop = WATER_Y - picture.tipH + sinkPx;
            const stemTop = floatTop + picture.h;
            const sit = tipSit(over, this.floatType);

            return {
                lineLen,
                lineTop,
                hookY,
                bedY,
                height: bedY + 30,
                waterY: WATER_Y,
                floatTop,
                stemTop,
                stemH: Math.max(0, lineTop - stemTop),
                kind: picture.kind,
                picture,
                sitTitle: sit.title,
                sitDetail: sit.detail,
                depthLabel: formatHeight(depthCm, this.unit),
                aria: `${floatTypeLabel(this.floatType)} rig diagram, ${sit.title}. ${groups
                    .map(({ g, text }) => `${text}, ${positionText(g, this.unit, depthCm)}`)
                    .join('; ')}`,
                groups: groups.map(({ g, y, text }, i) => ({
                    text,
                    position: positionText(g, this.unit, depthCm),
                    labelTop: tops[i],
                    marks: placementMarks(g, y),
                    leader: { x1: LINE_X + 12, y1: y, x2: LABEL_X - 4, y2: tops[i] + 10 },
                })),
            };
        },

        venueChosen(id) {
            return this.venueIds.map(String).includes(String(id));
        },

        /** The payload posted when saving the rig. */
        get placementsPayload() {
            return JSON.stringify(this.placements.map((group) => ({
                role: group.role,
                height_cm: Math.round(group.heightCm * 100) / 100,
                olivette_grams: group.olivetteGrams ?? null,
                anchor: group.anchor ?? 'hook',
                distance_unit: group.distanceUnit ?? (this.unit === 'ft' ? 'in' : 'cm'),
                note: group.note ?? null,
                items: group.items.map((item) => ({ size: item.size, count: item.count })),
            })));
        },

        get savePayload() {
            const pattern = this.active;

            return {
                float_name: this.floatName.trim(),
                float_size: this.floatSize.trim(),
                float_type: this.floatType,
                float_grams: this.ratedGrams ?? (this.parsed ? this.parsed.grams : ''),
                use_stored_grams: this.ratedGrams != null ? 1 : 0,
                depth: isFinite(this.depth) ? this.depth : '',
                depth_unit: this.unit,
                pattern_id: pattern ? pattern.id : '',
                olivette_grams: patternUsesOlivette(pattern?.id) ? (this.activeOlivette ?? '') : '',
            };
        },

        onSizeChange() {
            this.olivetteGrams = null;
            const parsed = parseFloatSize(this.floatSize);
            if (parsed && ! this.typeChosen) this.floatType = guessFloatType(parsed);
        },

        selectType(id) {
            this.floatType = id;
            this.typeChosen = true;
        },

        clearDots() {
            if (this.dots.length === 0 && this.dotPick === 'none') return;

            this.dots = [];
            this.dotPick = 'none';
        },

        resetLine() {
            this.clearDots();
            this.edits = null;
        },

        get canResetShots() {
            if (this.dots.length > 0 || this.dotPick !== 'none') return true;
            if (this.savedPlacements) return this.placementsPayload !== this.openingPlacements;

            return this.edits !== null;
        },

        /** Puts the line back to the shot you started with. */
        resetShots() {
            this.dots = [];
            this.dotPick = 'none';
            this.extraShot = 0;
            this.edits = this.savedPlacements ? groupsFromSaved(this.savedPlacements) : null;
        },

        ensureEdits() {
            if (this.edits || ! this.active) return;

            this.edits = cloneGroups(this.active.groups);
        },

        replaceEdit(key, update) {
            this.ensureEdits();
            this.edits = this.edits.map((group) => (group.key === key ? update(group) : group));
        },

        changeShot(key, itemIndex, size) {
            this.replaceEdit(key, (group) => ({
                ...group,
                items: group.items.map((item, index) => (index === itemIndex ? { ...item, size } : item)),
            }));
        },

        changeShotCount(key, itemIndex, count) {
            const next = Math.max(1, Math.min(12, Number(count) || 1));
            this.replaceEdit(key, (group) => ({
                ...group,
                items: group.items.map((item, index) => (index === itemIndex ? { ...item, count: next } : item)),
            }));
        },

        setPosition(key, patch) {
            this.replaceEdit(key, (group) => {
                const unit = patch.unit ?? group.distanceUnit ?? (this.unit === 'ft' ? 'in' : 'cm');
                const anchor = patch.anchor ?? group.anchor ?? 'hook';
                let heightCm = group.heightCm;

                if (patch.distance !== undefined && patch.distance !== '') {
                    const distance = Number(patch.distance);
                    if (! isFinite(distance) || distance < 0) return group;

                    const cm = unit === 'in' ? distance * 2.54 : distance;
                    heightCm = anchor === 'float' ? this.depthCm - cm : cm;
                }

                if (isFinite(this.depthCm)) {
                    heightCm = Math.min(Math.max(heightCm, 0), this.depthCm);
                }

                return { ...group, heightCm, distanceUnit: unit, anchor };
            });
        },

        get canAddLineShot() {
            return Boolean(this.active) && this.depthValid && this.placements.length < 30;
        },

        chosenAddSize() {
            return SHOT[this.addSize] ? this.addSize : 'No8';
        },

        get canAddToBulk() {
            if (! this.active || ! this.depthValid) return false;

            return this.canAddInto(this.placements.find((group) => group.role === 'bulk')
                ?? this.placements.find((group) => group.role === 'olivette'));
        },

        get canAddToLocking() {
            if (! this.active || ! this.depthValid) return false;

            return this.canAddInto(this.placements.find((group) => group.role === 'locking'));
        },

        canAddInto(target) {
            if (! target) return this.placements.length < 30;

            const existing = target.items.find((item) => item.size === this.chosenAddSize());
            if (existing) return existing.count < 12;

            return target.items.length < 8;
        },

        withChosenShot(group) {
            const size = this.chosenAddSize();
            const index = group.items.findIndex((item) => item.size === size);
            if (index >= 0) {
                return {
                    ...group,
                    items: group.items.map((item, itemIndex) => (
                        itemIndex === index ? { ...item, count: Math.min(12, item.count + 1) } : item
                    )),
                };
            }

            if (group.items.length >= 8) return group;

            return { ...group, items: [...group.items, { size, count: 1 }] };
        },

        /** Puts the chosen shot into the bulk, or starts a bulk when the rig has none. */
        addToBulk() {
            if (! this.canAddToBulk) return;

            this.ensureEdits();
            const target = this.edits.find((group) => group.role === 'bulk')
                ?? this.edits.find((group) => group.role === 'olivette');

            if (! target) {
                this.pushPlacedShot('bulk', this.depthCm * 0.65);

                return;
            }

            this.replaceEdit(target.key, (group) => this.withChosenShot(group));
        },

        /** Puts the chosen shot with the locking shot, at the float. */
        addToLocking() {
            if (! this.canAddToLocking) return;

            this.ensureEdits();
            const target = this.edits.find((group) => group.role === 'locking');

            if (! target) {
                this.extraShot += 1;
                this.edits = [
                    ...this.edits,
                    {
                        key: `locking-extra-${this.extraShot}`,
                        role: 'locking',
                        heightCm: this.depthCm,
                        olivetteGrams: null,
                        note: null,
                        anchor: 'hook',
                        distanceUnit: this.unit === 'ft' ? 'in' : 'cm',
                        items: [{ size: this.chosenAddSize(), count: 1 }],
                    },
                ];

                return;
            }

            this.replaceEdit(target.key, (group) => this.withChosenShot(group));
        },

        /** One shot on the line, with its own place, size and count. */
        addPlacedShot(role) {
            if (! this.canAddLineShot) return;

            this.ensureEdits();
            const depth = this.depthCm;
            const gap = Math.max(depth * 0.04, 4);
            const bulk = this.edits.find((group) => group.role === 'bulk')
                ?? this.edits.find((group) => group.role === 'olivette');
            let start = depth * 0.5;

            if (role === 'trim' && bulk) {
                const below = this.edits
                    .filter((group) => group.key !== bulk.key && group.heightCm < bulk.heightCm - 0.5)
                    .sort((a, b) => b.heightCm - a.heightCm)[0];
                start = below ? (bulk.heightCm + below.heightCm) / 2 : Math.max(bulk.heightCm - gap, 0);
            } else if (role === 'trim') {
                start = depth * 0.8;
            } else if (this.edits.length) {
                start = Math.max(Math.min(...this.edits.map((group) => group.heightCm)) - gap, 0);
            }

            this.pushPlacedShot(role, start);
        },

        pushPlacedShot(role, start) {
            const size = this.chosenAddSize();
            const depth = this.depthCm;
            let heightCm = Math.min(Math.max(start, 0), depth);
            let guard = 0;

            while (this.edits.some((group) => Math.abs(group.heightCm - heightCm) < 1.5) && guard < 12) {
                heightCm -= 1.5;
                if (heightCm < 0) heightCm = Math.max(depth - 1.5 * ((guard % 8) + 1), 0);
                guard += 1;
            }

            heightCm = Math.min(Math.max(heightCm, 0), depth);
            this.extraShot += 1;
            this.edits = [
                ...this.edits,
                {
                    key: `${role}-extra-${this.extraShot}`,
                    role,
                    heightCm,
                    olivetteGrams: null,
                    note: null,
                    anchor: 'hook',
                    distanceUnit: this.unit === 'ft' ? 'in' : 'cm',
                    items: [{ size, count: 1 }],
                },
            ];
        },

        addShot(key) {
            this.replaceEdit(key, (group) => {
                if (group.role !== 'bulk' && group.role !== 'olivette') return group;
                if (group.items.length >= 8) return group;

                const sizes = SHOTS.map((shot) => shot.size);
                let lightestAt = -1;
                for (const item of group.items) {
                    lightestAt = Math.max(lightestAt, sizes.indexOf(item.size));
                }
                const size = lightestAt < 0 ? 'No8' : (sizes[lightestAt + 1] ?? sizes[lightestAt]);

                return { ...group, items: [...group.items, { size, count: 1 }] };
            });
        },

        removeShot(key, itemIndex) {
            this.ensureEdits();
            this.edits = this.edits.flatMap((group) => {
                if (group.key !== key) return [group];

                const items = group.items.filter((_, index) => index !== itemIndex);
                if (! items.length && ! group.olivetteGrams) return [];

                return [{ ...group, items }];
            });
        },

        removePlacement(key) {
            this.ensureEdits();
            this.edits = this.edits.filter((group) => group.key !== key);
        },

        moveShot(key, direction) {
            this.ensureEdits();
            const sorted = this.edits
                .filter((group) => group.role !== 'stops')
                .sort((a, b) => b.heightCm - a.heightCm);
            const index = sorted.findIndex((group) => group.key === key);
            const next = direction === 'float' ? index - 1 : index + 1;
            if (index < 0 || next < 0 || next >= sorted.length) return;

            const height = sorted[index].heightCm;
            sorted[index].heightCm = sorted[next].heightCm;
            sorted[next].heightCm = height;
            this.edits = this.edits.map((group) => ({
                ...group,
                items: group.items.map((item) => ({ ...item })),
            }));
        },

        applyDots(choice) {
            this.dots = [...choice.shots];
            this.dotPick = choice.id;
        },

        addDot() {
            if (this.dots.length >= 6) return;

            this.dots = [...this.dots, this.dotSize];
            this.dotPick = 'custom';
        },

        changeDot(index, size) {
            this.dots = this.dots.map((dot, i) => (i === index ? size : dot));
            this.dotPick = 'custom';
        },

        removeDot(index) {
            this.dots = this.dots.filter((_, i) => i !== index);
            this.dotPick = 'custom';
        },

        /** Re-measures the labels so the layout can keep them clear of each other. */
        measureLabels() {
            const nodes = Array.from(this.$el.querySelectorAll('[data-shot-label]'));
            const next = nodes.map((node) => Math.ceil(node.getBoundingClientRect().height) || LABEL_ESTIMATE);
            const changed = next.length !== this.labelHeights.length || next.some((h, i) => h !== this.labelHeights[i]);
            if (changed) this.labelHeights = next;
        },

        formatGrams,
    };
}
