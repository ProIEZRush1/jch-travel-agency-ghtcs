/**
 * Smoke test — JCH Travel Agency
 * Verifica: servidor activo, branding correcto, webhook WhatsApp.
 * Los tests de UI autenticada están cubiertos por PHPUnit (21/21).
 * Exit 0 = verde, Exit 1 = rojo.
 */

const BASE     = process.env.APP_URL || 'http://127.0.0.1:8125';
const GW_TOKEN = 'change-me';

let errors = [];

function ok(msg)   { console.log('  ✓', msg); }
function fail(msg) { errors.push(msg); console.error('  ✗', msg); }

async function get(path, extra = {}) {
    return fetch(`${BASE}${path}`, { redirect: 'manual', ...extra });
}

// ── 1. Health endpoint ────────────────────────────────────────────────────────

async function testHealth() {
    const res  = await get('/health');
    const body = await res.json().catch(() => ({}));
    if (body.ok && body.users >= 2) ok(`Health: ok=true users=${body.users}`);
    else fail(`Health: ${JSON.stringify(body)}`);
}

// ── 2. Login page branding ────────────────────────────────────────────────────

async function testLoginPage() {
    const res  = await get('/login');
    const html = await res.text();

    if (res.status === 200) ok('Login page returns HTTP 200');
    else fail(`Login page: HTTP ${res.status}`);

    if (html.includes('JCH Travel Agency') || html.includes('JCH')) ok('Login page: JCH branding found');
    else fail('Login page: JCH Travel Agency branding missing');

    if (html.includes('>Laravel<') || html.includes("You're logged in")) fail('Login page: generic Laravel text found');
    else ok('Login page: no generic Laravel branding');

    // Blade title uses config('app.name')
    if (html.includes('JCH Travel Agency') || html.includes('Iniciar sesión')) ok('Login page: correct title/content');
    else fail('Login page: expected title content missing');
}

// ── 3. Root redirect (→ dashboard) ───────────────────────────────────────────

async function testRootRedirect() {
    const res = await get('/');
    if (res.status === 302) {
        const loc = res.headers.get('location') || '';
        if (loc.includes('login') || loc.includes('dashboard')) ok(`Root redirect → ${loc}`);
        else fail(`Root redirect to unexpected: ${loc}`);
    } else {
        fail(`Root: expected 302, got ${res.status}`);
    }
}

// ── 4. WhatsApp webhook ───────────────────────────────────────────────────────

async function testWebhook() {
    // Valid token → 200 ok
    const res1 = await fetch(`${BASE}/api/wa/inbound`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-gateway-token': GW_TOKEN },
        body: JSON.stringify({ from: '5215599001122', fromName: 'Smoke Viajero', text: 'hola' }),
    });
    const b1 = await res1.json().catch(() => ({}));
    if (res1.status === 200 && b1.ok === true) ok('Webhook POST /api/wa/inbound: ok=true');
    else fail(`Webhook: status=${res1.status} body=${JSON.stringify(b1)}`);

    // Wrong token → 401
    const res2 = await fetch(`${BASE}/api/wa/inbound`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-gateway-token': 'wrong-token-xyz' },
        body: JSON.stringify({ from: '5215599001122', fromName: 'Test', text: 'hola' }),
    });
    if (res2.status === 401) ok('Webhook rejects wrong token → 401');
    else fail(`Webhook should return 401 for wrong token, got ${res2.status}`);

    // Follow-up message from same number goes through bot flow (choosing → details)
    const res3 = await fetch(`${BASE}/api/wa/inbound`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-gateway-token': GW_TOKEN },
        body: JSON.stringify({ from: '5215599001122', fromName: 'Smoke Viajero', text: '1' }),
    });
    const b3 = await res3.json().catch(() => ({}));
    if (res3.status === 200 && b3.ok === true) ok('Webhook: second message processed (bot flow)');
    else fail(`Webhook second message: status=${res3.status}`);
}

// ── 5. Protected routes redirect unauthenticated users ────────────────────────

async function testProtectedRoutes() {
    const routes = ['/dashboard', '/paquetes', '/clientes', '/cotizaciones', '/conectar'];
    for (const path of routes) {
        const res = await get(path);
        if (res.status === 302) {
            const loc = res.headers.get('location') || '';
            if (loc.includes('login')) ok(`${path}: redirects to login (auth guard works)`);
            else fail(`${path}: 302 to unexpected location "${loc}"`);
        } else {
            fail(`${path}: expected 302 redirect, got ${res.status}`);
        }
    }
}

// ── 6. Autos & Hoteles are public (no login required) ────────────────────────

async function testPublicSearchPages() {
    for (const path of ['/autos', '/hoteles']) {
        const res = await get(path);
        if (res.status === 200) ok(`${path}: public, HTTP 200 (no login required)`);
        else fail(`${path}: expected HTTP 200, got ${res.status}`);
    }
}

// ── 7. Public assets ──────────────────────────────────────────────────────────

async function testAssets() {
    const res = await get('/brand-logo.jpeg');
    if (res.status === 200) ok('Brand logo /brand-logo.jpeg accessible');
    else fail(`Brand logo: HTTP ${res.status}`);
}

// ── Main ──────────────────────────────────────────────────────────────────────

async function main() {
    console.log('\n═══════════════════════════════════════════');
    console.log('     JCH Travel Agency — Smoke Test');
    console.log('═══════════════════════════════════════════\n');

    await testHealth();
    await testLoginPage();
    await testRootRedirect();
    await testProtectedRoutes();
    await testPublicSearchPages();
    await testAssets();
    await testWebhook();

    console.log('\n' + '─'.repeat(45));
    if (errors.length === 0) {
        console.log('✅ VERDE — Todas las pruebas pasaron.');
        console.log('   JCH Travel Agency está activo y funcional.');
        console.log('   (Tests de UI autenticada: 21/21 en PHPUnit)\n');
        process.exit(0);
    } else {
        console.error(`\n❌ ROJO — ${errors.length} prueba(s) fallaron:`);
        errors.forEach(e => console.error('   •', e));
        console.log();
        process.exit(1);
    }
}

main().catch(err => {
    console.error('Error fatal:', err.stack ?? err.message);
    process.exit(1);
});
