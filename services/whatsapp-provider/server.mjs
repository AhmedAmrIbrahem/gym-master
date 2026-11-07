import { resolve, join } from 'node:path';
import { createRequire } from 'node:module';
import { mkdir, stat } from 'node:fs/promises';
import { Ledger, MessageService } from './core.mjs';
import { createProviderServer } from './http.mjs';
import { WhatsAppSession } from './session.mjs';
import { acquireProviderLock } from './provider-lock.mjs';

const token = process.env.PROVIDER_TOKEN;
if (!token || token.length < 32) throw new Error('Set a random PROVIDER_TOKEN of at least 32 characters');
const adminToken = process.env.PROVIDER_ADMIN_TOKEN;
if (!adminToken || adminToken.length < 32 || adminToken === token) {
  throw new Error('Set a separate random PROVIDER_ADMIN_TOKEN of at least 32 characters');
}
const directory = resolve(process.env.PROVIDER_DATA_DIR || './data');
await mkdir(directory, { recursive: true, mode: 0o700 });
const releaseLock = await acquireProviderLock(directory);

let server;
let session;
let stopping = false;
let closing;

async function shutdown() {
  if (closing) return closing;
  stopping = true;
  closing = (async () => {
    // Allow in-flight HTTP requests to finish before closing the WhatsApp session.
    if (server?.listening) {
      await new Promise(resolve => server.close(() => resolve()));
    }
    try { await session?.close(); }
    catch { console.error('WhatsApp session shutdown failed; check for leftover Chrome processes.'); }
    await releaseLock();
  })();
  return closing;
}

function stopWithSignal(signal) {
  // Explicitly exit after async cleanup. Otherwise Ctrl+C may leave the process alive.
  shutdown().then(() => process.exit(0)).catch(() => process.exit(1));
}
process.on('SIGINT', stopWithSignal);
process.on('SIGTERM', stopWithSignal);

try {
  const require = createRequire(import.meta.url);
  const { Client, LocalAuth } = require('whatsapp-web.js');
  const qrcode = require('qrcode');
  session = new WhatsAppSession({ createClient: () => new Client({
    authStrategy: new LocalAuth({ clientId: 'gym', dataPath: join(directory, 'auth') }),
    webVersionCache: { type: 'local', path: join(directory, 'web-cache') },
    puppeteer: {
      headless: true,
      ...(process.env.CHROME_EXECUTABLE_PATH ? { executablePath: process.env.CHROME_EXECUTABLE_PATH } : {}),
    },
  }), encodeQr: qr => qrcode.toDataURL(qr, { width: 320, margin: 4, errorCorrectionLevel: 'M' }) });
  const service = new MessageService({ ledger: new Ledger(join(directory, 'ledger')), client: session, ready: () => session.isReady() });
  session.isSending = () => service.busy || service.activeOperations > 0;
  server = createProviderServer({ service, token, adminToken, session, ready: () => session.isReady() });
  server.on('error', error => {
    console.error('WhatsApp Provider HTTP listener failed:', error.code || 'unknown');
    shutdown().then(() => process.exit(1)).catch(() => process.exit(1));
  });
  await new Promise((resolveListen, rejectListen) => {
    server.once('listening', resolveListen);
    server.once('error', rejectListen);
    server.listen(Number(process.env.PROVIDER_PORT || 3100), process.env.PROVIDER_HOST || '127.0.0.1');
  });
  console.log('Provider HTTP listener started (private interface only recommended)');
  // Restore an existing session only. First-time pairing is started by the super admin.
  const hasSession = await stat(join(directory, 'auth', 'session-gym')).then(item => item.isDirectory()).catch(() => false);
  if (hasSession && !stopping) await session.connect();
} catch (error) {
  console.error('WhatsApp Provider startup failed:', error?.code || error?.message || 'unknown');
  await shutdown().catch(() => {});
  process.exitCode = 1;
}
