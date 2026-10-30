import { resolve, join } from 'node:path';
import { createRequire } from 'node:module';
import { mkdir, open, unlink, stat } from 'node:fs/promises';
import { Ledger, MessageService } from './core.mjs';
import { createProviderServer } from './http.mjs';
import { WhatsAppSession } from './session.mjs';

const token = process.env.PROVIDER_TOKEN;
if (!token || token.length < 32) throw new Error('Set a random PROVIDER_TOKEN of at least 32 characters');
const adminToken = process.env.PROVIDER_ADMIN_TOKEN;
if (!adminToken || adminToken.length < 32 || adminToken === token) {
  throw new Error('Set a separate random PROVIDER_ADMIN_TOKEN of at least 32 characters');
}
const directory = resolve(process.env.PROVIDER_DATA_DIR || './data');
await mkdir(directory, { recursive: true, mode: 0o700 });
// Single instance per data directory. A crash leaves a lock requiring operator review.
const lockPath = join(directory, 'provider.lock');
let lock;
try { lock = await open(lockPath, 'wx', 0o600); }
catch { throw new Error('Provider locked: verify no old process is running before removing data/provider.lock'); }
await lock.writeFile(String(process.pid));
await lock.close();

const require = createRequire(import.meta.url);
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const session = new WhatsAppSession({ createClient: () => new Client({
  authStrategy: new LocalAuth({ clientId: 'gym', dataPath: join(directory, 'auth') }),
  webVersionCache: { type: 'local', path: join(directory, 'web-cache') },
  puppeteer: {
    headless: true,
    ...(process.env.CHROME_EXECUTABLE_PATH ? { executablePath: process.env.CHROME_EXECUTABLE_PATH } : {}),
  },
}), encodeQr: qr => qrcode.toDataURL(qr, { width: 320, margin: 4, errorCorrectionLevel: 'M' }) });
const service = new MessageService({ ledger: new Ledger(join(directory, 'ledger')), client: session, ready: () => session.isReady() });
session.isSending = () => service.busy || service.activeOperations > 0;
const server = createProviderServer({ service, token, adminToken, session, ready: () => session.isReady() });
server.listen(Number(process.env.PROVIDER_PORT || 3100), process.env.PROVIDER_HOST || '127.0.0.1', () => {
  console.log('Provider HTTP listener started (private interface only recommended)');
});
server.on('error', () => { console.error('HTTP listener failed'); process.exitCode = 1; shutdown(); });
let stopping = false;
async function shutdown() {
  if (stopping) return;
  stopping = true;
  server.close();
  try { await session.close(); } catch { /* Keep shutdown logs free of session details. */ }
  await unlink(lockPath).catch(() => {});
}
process.on('SIGINT', () => shutdown());
process.on('SIGTERM', () => shutdown());
// Restore an existing session only. First-time pairing is started by the super admin.
const hasSession = await stat(join(directory, 'auth', 'session-gym')).then(item => item.isDirectory()).catch(() => false);
if (hasSession && !stopping) await session.connect();
