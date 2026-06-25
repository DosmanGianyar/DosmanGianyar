const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    makeInMemoryStore,
} = require('@whiskeysockets/baileys');
const { Boom }   = require('@hapi/boom');
const express    = require('express');
const qrcode     = require('qrcode-terminal');
const pino       = require('pino');
const path       = require('path');

// ── Konfigurasi ───────────────────────────────────────────────────────────
const PORT          = process.env.PORT   || 3000;
const SECRET        = process.env.SECRET || '';          // kosongkan = tanpa auth
const AUTH_DIR      = path.join(__dirname, 'auth_info'); // folder simpan sesi WA

// ── Express ───────────────────────────────────────────────────────────────
const app = express();
app.use(express.json());

// Middleware: cek secret key jika dikonfigurasi
app.use((req, res, next) => {
    if (!SECRET) return next();
    const key = req.headers['x-api-key'] || req.body?.secret;
    if (key !== SECRET) return res.status(401).json({ success: false, message: 'Unauthorized' });
    next();
});

// ── State socket ──────────────────────────────────────────────────────────
let sock        = null;
let isConnected = false;
let qrString    = null;

// ── Format nomor WA ───────────────────────────────────────────────────────
function formatPhone(phone) {
    phone = String(phone).replace(/\D/g, ''); // hapus non-digit
    if (phone.startsWith('0'))  phone = '62' + phone.slice(1);
    if (!phone.startsWith('62')) phone = '62' + phone;
    return phone + '@s.whatsapp.net';
}

// ── Inisialisasi Baileys ──────────────────────────────────────────────────
async function startBaileys() {
    const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

    sock = makeWASocket({
        auth                   : state,
        logger                 : pino({ level: 'silent' }),
        printQRInTerminal      : true,   // tampilkan QR di terminal juga
        browser                : ['SIMS-Gateway', 'Chrome', '124.0.0'],
        connectTimeoutMs       : 60_000,
        defaultQueryTimeoutMs  : 60_000,
        keepAliveIntervalMs    : 15_000,
        retryRequestDelayMs    : 2_000,
        qrTimeout              : 60_000,
    });

    // Event: QR Code
    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            qrString = qr;
            console.log('\n==============================');
            console.log('  Scan QR code berikut dengan WhatsApp HP sekolah:');
            console.log('==============================\n');
            qrcode.generate(qr, { small: true });
            console.log('\nAtau buka http://localhost:' + PORT + '/qr di browser\n');
        }

        if (connection === 'close') {
            isConnected = false;
            const statusCode   = (lastDisconnect?.error instanceof Boom)
                ? lastDisconnect.error.output.statusCode
                : 0;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

            console.log(`Koneksi terputus (kode: ${statusCode}). Reconnect: ${shouldReconnect}`);

            if (statusCode === DisconnectReason.loggedOut) {
                console.log('Sesi logout. Hapus folder auth_info lalu restart untuk scan ulang.');
            } else if (statusCode === DisconnectReason.restartRequired) {
                console.log('Restart diperlukan...');
                setTimeout(startBaileys, 2000);
            } else if (shouldReconnect) {
                // Tunggu lebih lama sebelum reconnect agar tidak flood
                setTimeout(startBaileys, 5000);
            }
        }

        if (connection === 'open') {
            isConnected = true;
            qrString    = null;
            console.log('✅ WhatsApp terhubung!');
        }
    });

    // Event: simpan kredensial
    sock.ev.on('creds.update', saveCreds);
}

// ── Endpoint: kirim pesan ─────────────────────────────────────────────────
app.post('/send', async (req, res) => {
    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ success: false, message: 'phone dan message wajib diisi.' });
    }

    if (!isConnected || !sock) {
        return res.status(503).json({ success: false, message: 'WhatsApp belum terhubung. Scan QR terlebih dahulu.' });
    }

    try {
        const jid = formatPhone(phone);
        await sock.sendMessage(jid, { text: message });
        console.log(`✉️  Pesan terkirim ke ${phone}`);
        return res.json({ success: true, message: 'Pesan berhasil dikirim.' });
    } catch (err) {
        console.error('Gagal kirim pesan:', err.message);
        return res.status(500).json({ success: false, message: err.message });
    }
});

// ── Endpoint: status koneksi ──────────────────────────────────────────────
app.get('/status', (req, res) => {
    res.json({ connected: isConnected });
});

// ── Endpoint: tampilkan QR di browser ────────────────────────────────────
app.get('/qr', (req, res) => {
    if (isConnected) {
        return res.send('<h2>✅ WhatsApp sudah terhubung!</h2>');
    }
    if (!qrString) {
        return res.send('<h2>Menunggu QR code... Refresh halaman ini.</h2><script>setTimeout(()=>location.reload(),3000)</script>');
    }

    // Render QR sebagai gambar via qrcode library
    const QRCode = require('qrcode');
    QRCode.toDataURL(qrString, (err, url) => {
        if (err) return res.send('Error generate QR');
        res.send(`
            <!DOCTYPE html><html><head><title>SIMS WA Gateway QR</title></head>
            <body style="font-family:sans-serif;text-align:center;padding:40px">
                <h2>Scan QR Code dengan WhatsApp HP Sekolah</h2>
                <img src="${url}" style="width:300px;height:300px"/>
                <p style="color:#888">QR akan refresh otomatis setiap 5 detik</p>
                <script>setTimeout(()=>location.reload(), 5000)</script>
            </body></html>
        `);
    });
});

// ── Start ─────────────────────────────────────────────────────────────────
app.listen(PORT, () => {
    console.log(`\n🚀 Baileys WA Gateway berjalan di port ${PORT}`);
    console.log(`   Status : http://localhost:${PORT}/status`);
    console.log(`   QR     : http://localhost:${PORT}/qr\n`);
});

startBaileys();
