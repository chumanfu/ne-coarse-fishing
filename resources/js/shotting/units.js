export function depthToCm(value, unit) {
    return unit === 'ft' ? value * 30.48 : value * 100;
}

export function formatDepth(value, unit) {
    return `${value}${unit === 'ft' ? 'ft' : 'm'}`;
}

/** Formats a distance above the hook in the angler's preferred units. */
export function formatHeight(cm, unit) {
    if (unit === 'm') {
        return cm >= 100 ? `${(cm / 100).toFixed(2)}m` : `${Math.round(cm)}cm`;
    }

    const inches = Math.round(cm / 2.54);
    if (inches < 24) return `${inches}in`;
    const ft = Math.floor(inches / 12);
    const rem = inches % 12;

    return rem ? `${ft}ft ${rem}in` : `${ft}ft`;
}
