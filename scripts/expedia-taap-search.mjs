// Live Expedia TAAP hotel search for JCH Travel Agency.
//
// Invoked by App\Services\ExpediaTaapClient as:
//   node scripts/expedia-taap-search.mjs <destino> <checkin> <checkout> <adultos> <sessionPath>
//
// Reads EXPEDIA_TAAP_USER / EXPEDIA_TAAP_PASS / EXPEDIA_TAAP_URL from the environment (never
// from argv, so credentials never show up in `ps`/process listings). Logs into the real TAAP
// agent portal with a headless Chromium session, reuses/renews the session cookies at
// <sessionPath>, then navigates to the real Hotel-Search results URL and scrapes whatever
// TAAP actually rendered. Writes a single JSON object to stdout and NOTHING else — every log
// line goes to stderr so stdout stays parseable.
//
// This script NEVER invents a hotel or a price: on any failure it prints
// {success:false, error:"..."} and the PHP caller is responsible for surfacing an honest
// "couldn't connect right now" message instead of fabricating results.

import { chromium } from 'playwright';
import fs from 'node:fs';

const LOGIN_TIMEOUT_MS = 25000;
const NAV_TIMEOUT_MS = 30000;
const RESULTS_TIMEOUT_MS = 30000;

function log(...args) {
    console.error('[expedia-taap-search]', ...args);
}

function emit(payload) {
    process.stdout.write(JSON.stringify(payload));
}

async function looksLoggedOut(page) {
    const url = page.url();
    if (/\/(login|logon|signin)/i.test(url)) return true;

    return (await page.locator('input[type="password"]').count()) > 0;
}

async function login(browser, baseUrl, user, pass) {
    const context = await browser.newContext();
    const page = await context.newPage();
    page.setDefaultTimeout(LOGIN_TIMEOUT_MS);

    await page.goto(baseUrl, { waitUntil: 'domcontentloaded', timeout: NAV_TIMEOUT_MS });

    const userField = page
        .locator('input[name="username"], input[name="UserName"], input[type="email"], #username, #UserName')
        .first();
    const passField = page
        .locator('input[name="password"], input[name="Password"], input[type="password"], #password, #Password')
        .first();

    await userField.waitFor({ state: 'visible', timeout: LOGIN_TIMEOUT_MS });
    await userField.fill(user);
    await passField.fill(pass);

    const submit = page
        .locator('button[type="submit"], input[type="submit"], button:has-text("Iniciar"), button:has-text("Sign in"), button:has-text("Log in")')
        .first();

    await Promise.all([
        page.waitForLoadState('networkidle', { timeout: NAV_TIMEOUT_MS }).catch(() => {}),
        submit.click(),
    ]);

    if (await looksLoggedOut(page)) {
        await context.close();
        throw new Error('login_failed');
    }

    return { context, page };
}

async function textOf(scope, selector) {
    const loc = scope.locator(selector).first();
    if ((await loc.count()) === 0) return null;
    const txt = await loc.innerText().catch(() => null);
    return txt ? txt.trim() : null;
}

async function attrOf(scope, selector, attr) {
    const loc = scope.locator(selector).first();
    if ((await loc.count()) === 0) return null;
    return loc.getAttribute(attr).catch(() => null);
}

async function scrapeHotelCards(page) {
    // Expedia's Blossom/EGDS listing components identify hotel cards with `data-stid`
    // attributes containing "property-listing". Kept broad (contains-match) since Expedia
    // revisions the exact stid suffix periodically; if this stops matching, the search simply
    // returns zero cards and the caller reports "unavailable" — it never fabricates a result.
    const cardSelector = '[data-stid*="property-listing"]';

    await page
        .waitForSelector(
            `${cardSelector}, [data-stid="no-search-results"], text=/no encontramos resultados|sin resultados|no results/i`,
            { timeout: RESULTS_TIMEOUT_MS },
        )
        .catch(() => {});

    const cards = await page.locator(cardSelector).all();
    const hoteles = [];

    for (const card of cards) {
        const nombre = await textOf(card, '[data-stid="content-hotel-title"], h3, [itemprop="name"]');
        if (!nombre) continue;

        const estrellasLabel = await attrOf(
            card,
            '[aria-label*="estrella"], [aria-label*="star"], [data-stid*="rating"]',
            'aria-label',
        );
        const estrellasMatch = estrellasLabel ? estrellasLabel.match(/\d+(\.\d+)?/) : null;

        const planAlimentos = await textOf(
            card,
            '[data-stid*="meal-plan"], [data-test-id*="meal-plan"], [data-stid*="board-basis"]',
        );

        const precioNocheTexto = await textOf(
            card,
            '[data-stid="price-summary-message-line"], [data-test-id="price-per-night"], [data-stid*="lead-rate"]',
        );

        const precioTotalTexto = await textOf(
            card,
            '[data-stid="price-summary"] [aria-hidden="true"], [data-test-id="price-total"], [data-stid*="pricing-total"]',
        );

        const imagen = await attrOf(card, 'img', 'src');

        hoteles.push({
            nombre,
            estrellas: estrellasMatch ? Number(estrellasMatch[0]) : null,
            plan_alimentos: planAlimentos,
            precio_noche_texto: precioNocheTexto,
            precio_total_texto: precioTotalTexto,
            imagen,
        });
    }

    return hoteles;
}

async function main() {
    const [, , destino, checkin, checkout, adultosRaw, sessionPath] = process.argv;
    const adultos = Number(adultosRaw) || 2;
    const baseUrl = (process.env.EXPEDIA_TAAP_URL || 'https://www.expediataap.mx').replace(/\/+$/, '');
    const user = process.env.EXPEDIA_TAAP_USER || '';
    const pass = process.env.EXPEDIA_TAAP_PASS || '';

    if (!destino || !checkin || !checkout || !sessionPath) {
        emit({ success: false, error: 'missing_arguments' });
        return;
    }

    if (!user || !pass) {
        emit({ success: false, error: 'missing_credentials' });
        return;
    }

    const browser = await chromium.launch({ headless: true });

    try {
        let context;
        let page;
        let reused = false;

        if (fs.existsSync(sessionPath)) {
            try {
                context = await browser.newContext({ storageState: sessionPath });
                page = await context.newPage();
                page.setDefaultTimeout(NAV_TIMEOUT_MS);
                await page.goto(baseUrl, { waitUntil: 'domcontentloaded', timeout: NAV_TIMEOUT_MS });
                reused = !(await looksLoggedOut(page));
                if (!reused) await context.close();
            } catch (err) {
                log('stale session, discarding', err?.message || err);
                reused = false;
            }
        }

        if (!reused) {
            log('logging in to TAAP');
            ({ context, page } = await login(browser, baseUrl, user, pass));
            await context.storageState({ path: sessionPath });
        } else {
            log('reused cached TAAP session');
        }

        page.setDefaultTimeout(RESULTS_TIMEOUT_MS);

        const searchUrl =
            `${baseUrl}/Hotel-Search?destination=${encodeURIComponent(destino)}` +
            `&startDate=${encodeURIComponent(checkin)}&endDate=${encodeURIComponent(checkout)}` +
            `&rooms=1&adults=${adultos}&rate_type=standalone&sort=RECOMMENDED`;

        log('navigating to', searchUrl);
        await page.goto(searchUrl, { waitUntil: 'domcontentloaded', timeout: NAV_TIMEOUT_MS });

        if (await looksLoggedOut(page)) {
            // Session was valid a moment ago but TAAP bounced us to login anyway (expired
            // mid-flight) — retry once with a fresh login rather than reporting a false negative.
            log('session expired mid-search, re-authenticating');
            await context.close();
            ({ context, page } = await login(browser, baseUrl, user, pass));
            await context.storageState({ path: sessionPath });
            page.setDefaultTimeout(RESULTS_TIMEOUT_MS);
            await page.goto(searchUrl, { waitUntil: 'domcontentloaded', timeout: NAV_TIMEOUT_MS });
        }

        const hoteles = await scrapeHotelCards(page);

        emit({ success: true, hoteles });
    } catch (err) {
        log('search failed', err?.message || err);
        emit({ success: false, error: String((err && err.message) || err) });
    } finally {
        await browser.close().catch(() => {});
    }
}

main().catch((err) => {
    log('fatal', err?.message || err);
    emit({ success: false, error: String((err && err.message) || err) });
});
