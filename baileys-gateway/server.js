const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    makeCacheableSignalKeyStore,
} = require('@whiskeysockets/baileys');
const { Boom }  = require('@hapi/boom');
const express   = require('express');
const pino      = require('pino');
const path      = require('path');
const readline  = require('readline');

// ── Konfigurasi ───────────────────────────────────────────────────────────
const PORT     = process.env.PORT   || 3000;
const SECRET   = process.env.SECRET || '';
const AUTH_DIR = path.join(__dirname, 'auth_info');

// Nomor HP pengirim WA (format: 628xxx tanpa + dan spasi)
// Diisi via env SENDER_PHONE atau diinput manual saat pertama kali
let SENDER_PHONE = process.env.SENDER_PHONE || '';

// ── Express ───────────────────────────────────────────────────────────────
const app = express();
app.use(express.json());

app.use((req, res, next) => {
    if (!SECRET) return next();
    const key = req.headers['x-api-key'] || req.body?.secret;
    if (key !== SECRET) return res.status(401).json({ success: false, message: 'Unauthorized' });
    next();
});

// ── State ─────────────────────────────────────────────────────────────────
let sock        = null;
let isConnected = false;
let pairingCode = null;

// ── Format nomor ──────────────────────────────────────────────────────────
function formatPhone(phone) {
    phone = String(phone).replace(/\D/g, '');
    if (phone.startsWith('0'))   phone = '62' + phone.slice(1);
    if (!phone.startsWith('62')) phone = '62' + phone;
    return phone + '@s.whatsapp.net';
}

function cleanPhone(phone) {
    phone = String(phone).replace(/\D/g, '');
    if (phone.startsWith('0')) phone = '62' + phone.slice(1);
    if (!phone.startsWith('62')) phone = '62' + phone;
    return phone;
}

// ── Input nomor dari terminal ──────────────────────────────────────────────
function promptPhone() {
    return new Promise((resolve) => {
        const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
        rl.question('\n📱 Masukkan nomor WA pengirim (format: 628xxx): ', (answer) => {
            rl.close();
            resolve(answer.trim());
        });
    });
}

// ── Inisialisasi Baileys ──────────────────────────────────────────────────
async function startBaileys() {
    const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

    sock = makeWASocket({
        auth: {
            creds: state.creds,
            keys : makeCacheableSignalKeyStore(state.keys, pino({ level: 'silent' })),
        },
        logger              : pino({ level: 'silent' }),
        connectTimeoutMs    : 60_000,
        keepAliveIntervalMs : 15_000,
        browser             : ['SIMS-Gateway', 'Chrome', '124.0.0'],
    });

    // Pairing code — minta nomor jika belum ada
    if (!sock.authState.creds.registered) {
        if (!SENDER_PHONE) {
            SENDER_PHONE = await promptPhone();
        }
        const phone = cleanPhone(SENDER_PHONE);

        await new Promise(r => setTimeout(r, 2000)); // tunggu socket siap

        try {
            pairingCode = await sock.requestPairingCode(phone);
            console.log('\n==============================');
            console.log('  KODE PAIRING WhatsApp:');
            console.log(`  👉  ${pairingCode}`);
            console.log('==============================');
            console.log('Langkah:');
            console.log('1. Buka WhatsApp di HP');
            console.log('2. Ketuk Titik tiga → Perangkat tertaut → Tautkan perangkat');
            console.log('3. Pilih "Tautkan dengan nomor telepon"');
            console.log(`4. Masukkan kode: ${pairingCode}\n`);
        } catch (err) {
            console.error('Gagal mendapatkan pairing code:', err.message);
        }
    }

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect } = update;

        if (connection === 'close') {
            isConnected  = false;
            pairingCode  = null;
            const status = (lastDisconnect?.error instanceof Boom)
                ? lastDisconnect.error.output.statusCode
                : 0;

            console.log(`Koneksi terputus (kode: ${status}).`);

            if (status === DisconnectReason.loggedOut) {
                console.log('Logout. Hapus folder auth_info lalu restart.');
            } else {
                console.log('Mencoba reconnect dalam 5 detik...');
                setTimeout(startBaileys, 5000);
            }
        }

        if (connection === 'open') {
            isConnected = true;
            pairingCode = null;
            console.log('✅ WhatsApp terhubung! Siap kirim pesan.');
        }
    });

    sock.ev.on('creds.update', saveCreds);
}

// ── Endpoint: kirim pesan ─────────────────────────────────────────────────
app.post('/send', async (req, res) => {
    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ success: false, message: 'phone dan message wajib diisi.' });
    }
    if (!isConnected || !sock) {
        return res.status(503).json({ success: false, message: 'WhatsApp belum terhubung.' });
    }

    try {
        await sock.sendMessage(formatPhone(phone), { text: message });
        console.log(`✉️  Pesan terkirim ke ${phone}`);
        return res.json({ success: true, message: 'Pesan berhasil dikirim.' });
    } catch (err) {
        console.error('Gagal kirim:', err.message);
        return res.status(500).json({ success: false, message: err.message });
    }
});

// ── Endpoint: status + pairing code ──────────────────────────────────────
app.get('/status', (req, res) => {
    res.json({
        connected   : isConnected,
        pairingCode : pairingCode ?? null,
    });
});

app.get('/pairing', (req, res) => {
    if (isConnected) {
        return res.send('<h2 style="font-family:sans-serif;padding:40px">✅ WhatsApp sudah terhubung!</h2>');
    }
    if (!pairingCode) {
        return res.send(`
            <h2 style="font-family:sans-serif;padding:40px">Menunggu pairing code...</h2>
            <script>setTimeout(()=>location.reload(),3000)</script>
        `);
    }
    res.send(`
        <!DOCTYPE html><html><head><title>SIMS WA Pairing</title></head>
        <body style="font-family:sans-serif;text-align:center;padding:60px;background:#f0f4ff">
            <h2>🔗 Tautkan WhatsApp ke SIMS</h2>
            <div style="font-size:48px;font-weight:bold;letter-spacing:12px;
                background:#fff;border:3px solid #2563eb;border-radius:16px;
                padding:20px 40px;display:inline-block;margin:20px 0;color:#1e3a8a">
                ${pairingCode}
            </div>
            <p style="color:#555;max-width:400px;margin:auto">
                1. Buka WhatsApp di HP<br>
                2. Titik tiga → <b>Perangkat tertaut</b><br>
                3. <b>Tautkan perangkat</b> → <b>Tautkan dengan nomor telepon</b><br>
                4. Masukkan kode di atas
            </p>
            <script>setTimeout(()=>location.reload(),10000)</script>
        </body></html>
    `);
});

// ── Start ─────────────────────────────────────────────────────────────────
app.listen(PORT, () => {
    console.log(`\n🚀 Baileys WA Gateway berjalan di port ${PORT}`);
    console.log(`   Status  : http://localhost:${PORT}/status`);
    console.log(`   Pairing : http://localhost:${PORT}/pairing\n`);
});

startBaileys();
