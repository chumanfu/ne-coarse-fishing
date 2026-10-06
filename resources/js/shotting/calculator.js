import { FLOAT_TYPES, formatGrams, guessFloatType, parseFloatSize } from './floats';
import { describeItems } from './shots';
import { GENERAL_TIPS, MIN_DEPTH_CM, generatePatterns, groupGrams, olivetteOptions } from './shotting';
import { depthToCm, formatHeight } from './units';

const ROLE_LABEL = {
    locking: 'Locking',
    bulk: 'Bulk',
    olivette: 'Olivette',
    dropper: 'Dropper',
    strung: 'Strung',
    trim: 'Trim',
};

const LINE_X = 48;
const LABEL_X = 96;
const DIAGRAM_TOP = 72; // where the line leaves the float
const LABEL_SPACING = 6;
const LABEL_ESTIMATE = 36; // used until a label has been measured

function groupText(g) {
    const parts = [];
    if (g.olivetteGrams) parts.push(`${g.olivetteGrams}g olivette`);
    if (g.items.length) parts.push(describeItems(g.items));

    return parts.join(' + ');
}

function positionText(g, unit) {
    if (g.role === 'locking') return 'At float';

    return `${formatHeight(g.heightCm, unit)} from hook`;
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
        diagramTop: DIAGRAM_TOP,

        floatName: config.floatName ?? '',
        floatSize: config.floatSize ?? '',
        floatType: config.floatType ?? 'pole',
        typeChosen: Boolean(config.floatType),
        depthText: config.depth ? String(config.depth) : '',
        unit: config.depthUnit ?? 'ft',
        patternId: config.patternId ?? null,
        olivetteGrams: config.olivetteGrams ?? null,
        labelHeights: [],

        venues: config.venues ?? [],
        venueId: config.venueId ?? '',
        pegId: config.pegId ?? '',

        init() {
            // The peg options are rendered by Alpine, so the preselected peg has to
            // wait until they exist or the select falls back to its empty option.
            const preselectedPeg = this.pegId;
            this.pegId = '';
            this.$nextTick(() => {
                this.pegId = preselectedPeg;
            });

            this.remeasure = () => this.measureLabels();
            window.addEventListener('resize', this.remeasure);
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

            return this.parsed ? this.parsed.explanation : 'Not recognised – try a format like 4x12, 0.3g or 2AAA';
        },

        get patterns() {
            if (! this.parsed || ! this.depthValid) return [];

            return generatePatterns({
                floatGrams: this.parsed.grams,
                floatType: this.floatType,
                depthCm: this.depthCm,
                olivetteGrams: this.olivetteGrams ?? undefined,
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

        get loadSummary() {
            const pattern = this.active;
            if (! pattern) return null;
            const diff = pattern.floatGrams - pattern.loadGrams;

            return {
                heading: `Shot load ${formatGrams(pattern.loadGrams)} of ${formatGrams(pattern.floatGrams)}`,
                detail:
                    diff > 0.015
                        ? `About ${formatGrams(diff)} light – add a small shot under the float to dot the tip down.`
                        : 'Should sit the float with just the tip showing.',
            };
        },

        /** Table rows for the pattern, in the same order as the diagram. */
        get rows() {
            if (! this.active) return [];

            return this.active.groups.map((g) => ({
                role: ROLE_LABEL[g.role],
                text: groupText(g),
                position: positionText(g, this.unit),
                note: g.note ?? null,
                grams: formatGrams(groupGrams(g)),
            }));
        },

        get diagram() {
            const pattern = this.active;
            if (! pattern || ! this.depthValid) return null;

            const depthCm = this.depthCm;
            const lineLen = Math.min(420, Math.max(220, depthCm * 1.1));
            const hookY = DIAGRAM_TOP + lineLen;
            const yFor = (h) => DIAGRAM_TOP + (1 - h / depthCm) * lineLen;

            const groups = pattern.groups.map((g) => ({ g, y: yFor(g.heightCm), text: groupText(g) }));
            const heights = groups.map((_, i) => this.labelHeights[i] ?? LABEL_ESTIMATE);
            const tops = layoutLabels(
                groups.map(({ y }) => y),
                heights,
                DIAGRAM_TOP - 20,
                hookY + 10,
            );
            const labelsBottom = tops.length ? tops[tops.length - 1] + heights[heights.length - 1] : 0;
            const bedY = Math.max(hookY + 14, labelsBottom + 8);

            return {
                lineLen,
                hookY,
                bedY,
                height: bedY + 30,
                depthLabel: formatHeight(depthCm, this.unit),
                aria: `Rig diagram: ${groups
                    .map(({ g, text }) => `${text}, ${positionText(g, this.unit)}`)
                    .join('; ')}`,
                groups: groups.map(({ g, y, text }, i) => {
                    const grams = groupGrams(g);
                    const size = Math.max(7, Math.min(22, 6 + Math.sqrt(grams) * 14));

                    return {
                        text,
                        position: positionText(g, this.unit),
                        isOlivette: g.role === 'olivette',
                        isLocking: g.role === 'locking',
                        y,
                        labelTop: tops[i],
                        markerStyle:
                            g.role === 'olivette'
                                ? `width:12px;height:26px;border-radius:6px;top:${y - 13}px;left:${LINE_X - 6}px`
                                : `width:${size}px;height:${size}px;border-radius:${size / 2}px;top:${y - size / 2}px;left:${LINE_X - size / 2}px`,
                        leader: { x1: LINE_X + 12, y1: y, x2: LABEL_X - 4, y2: tops[i] + 10 },
                    };
                }),
            };
        },

        get pegOptions() {
            return this.venues.find((v) => v.id === Number(this.venueId))?.pegs ?? [];
        },

        onVenueChange() {
            if (! this.pegOptions.some((peg) => peg.id === Number(this.pegId))) {
                this.pegId = '';
            }
        },

        /** The payload posted when saving the pattern against a peg. */
        get savePayload() {
            const pattern = this.active;

            return {
                float_name: this.floatName.trim(),
                float_size: this.floatSize.trim(),
                float_type: this.floatType,
                float_grams: this.parsed ? this.parsed.grams : '',
                depth: isFinite(this.depth) ? this.depth : '',
                depth_unit: this.unit,
                pattern_id: pattern ? pattern.id : '',
                olivette_grams: pattern?.id === 'olivette' ? (this.activeOlivette ?? '') : '',
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
