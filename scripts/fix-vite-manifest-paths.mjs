import { readFileSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const manifestPath = resolve('public/build/manifest.json');
const buildPrefix = '../public/build/';

const manifest = JSON.parse(readFileSync(manifestPath, 'utf8'));

const withPrefix = (value) => {
    if (typeof value !== 'string' || value.startsWith(buildPrefix)) {
        return value;
    }

    return `${buildPrefix}${value}`;
};

for (const entry of Object.values(manifest)) {
    if (!entry || typeof entry !== 'object') {
        continue;
    }

    if ('file' in entry) {
        entry.file = withPrefix(entry.file);
    }

    if (Array.isArray(entry.css)) {
        entry.css = entry.css.map(withPrefix);
    }
}

writeFileSync(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`);
