import { createHash, timingSafeEqual } from 'node:crypto';
import { mkdir, open, readFile } from 'node:fs/promises';
import { join } from 'node:path';

const uuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
const response = (code, status) => ({ code, body: { status } });

export function authenticated(header, token) {
  if (!token || token.length < 32 || typeof header !== 'string') return false;
  const a = Buffer.from(header), b = Buffer.from(`Bearer ${token}`);
  return a.length === b.length && timingSafeEqual(a, b);
}

// One file per key, created exclusively and synced BEFORE calling WhatsApp.
// Persist data across restarts; never delete uncertain records to replay them.
export class Ledger {
  constructor(directory) { this.directory = directory; }
  path(key) {
    if (!uuid.test(key)) throw new Error('invalid_key');
    return join(this.directory, `${key.toLowerCase()}.json`);
  }
  async get(key) {
    try {
      const lines = (await readFile(this.path(key), 'utf8')).split('\n');
      return JSON.parse(lines.at(-1));
    }
    catch (error) { if (error.code === 'ENOENT') return null; throw error; }
  }
  async reserve(key, fingerprint) {
    await mkdir(this.directory, { recursive: true, mode: 0o700 });
    let handle;
    try {
      handle = await open(this.path(key), 'wx', 0o600);
      await handle.writeFile(JSON.stringify({ status: 'unknown', fingerprint }));
      await handle.sync();
      return true;
    } catch (error) { if (error.code === 'EEXIST') return false; throw error; }
    finally { await handle?.close(); }
  }
  async markSent(key, fingerprint) {
    const handle = await open(this.path(key), 'a', 0o600);
    try {
      await handle.writeFile('\n' + JSON.stringify({ status: 'sent', fingerprint }));
      await handle.sync();
    } finally { await handle.close(); }
  }
}

export class MessageService {
  constructor({ ledger, client, ready, timeoutMs = 30000, report = info => console.error('[gym-whatsapp]', JSON.stringify(info)) }) {
    Object.assign(this, { ledger, client, ready, timeoutMs, report });
    this.busy = false;
    this.activeOperations = 0;
  }
  diagnostic(stage, reason, error = null) {
    const code = typeof error?.code === 'string' && /^[A-Z_0-9]{1,40}$/.test(error.code) ? error.code : null;
    const message = String(error?.message ?? '').toLowerCase();
    const hint = /_serialized|serializ|getmessagemodel/.test(message) ? 'message_serialization'
      : /getlastmsgkeyforaction|markedunread|sendseen/.test(message) ? 'whatsapp_api_changed'
      : /execution context|target closed|session closed/.test(message) ? 'browser_context_lost'
      : message === 'timeout' ? 'timeout' : null;
    // Never output raw errors: they may contain recipient, message, credentials or QR data.
    try { this.report({ stage, reason, code, hint }); } catch { /* Logging must not alter send semantics. */ }
  }
  async limited(operation) {
    let timer;
    this.activeOperations++;
    const pending = Promise.resolve().then(operation);
    pending.then(() => { this.activeOperations--; }, () => { this.activeOperations--; });
    try {
      return await Promise.race([
        pending,
        new Promise((_, reject) => { timer = setTimeout(() => reject(new Error('timeout')), this.timeoutMs); }),
      ]);
    } finally { clearTimeout(timer); }
  }
  async send(input) {
    if (!input || typeof input !== 'object' || !uuid.test(input.idempotency_key ?? '')
      || typeof input.phone !== 'string' || !/^\+[1-9][0-9]{7,14}$/.test(input.phone)
      || typeof input.text !== 'string' || input.text.length < 1 || input.text.length > 1500) {
      return response(400, 'invalid_request');
    }
    const { idempotency_key: key, phone, text } = input;
    const fingerprint = createHash('sha256').update(JSON.stringify([phone, text])).digest('hex');
    let stage = 'ledger_read';
    try {
      const existing = await this.ledger.get(key);
      if (existing) {
        if (existing.fingerprint !== fingerprint) { this.diagnostic(stage, 'key_conflict'); return response(409, 'key_conflict'); }
        if (existing.status !== 'sent') this.diagnostic('cached_unknown', 'not_replayed');
        return response(existing.status === 'sent' ? 200 : 409, existing.status);
      }
      // A busy/disconnected provider DEFINITELY has not accepted this new key.
      if (!this.ready() || this.busy || this.activeOperations > 0) return response(503, 'not_ready');
      this.busy = true;
      try {
        let number;
        stage = 'recipient_lookup';
        try { number = await this.limited(() => this.client.getNumberId(phone.slice(1))); }
        catch (error) { this.diagnostic(stage, 'lookup_failed', error); return response(503, 'not_ready'); } // Lookup cannot send a message.
        if (!number) return response(422, 'invalid_recipient');
        stage = 'ledger_reserve';
        if (!await this.ledger.reserve(key, fingerprint)) return response(409, 'unknown');
        // From here onwards any exception is uncertain, and this key is NEVER auto-replayed.
        stage = 'whatsapp_send';
        const message = await this.limited(() => this.client.sendMessage(number._serialized, text, { sendSeen: false, waitUntilMsgSent: true }));
        if (!message?.id) { this.diagnostic(stage, 'send_unconfirmed'); return response(409, 'unknown'); }
        stage = 'ledger_write';
        await this.ledger.markSent(key, fingerprint);
        return response(200, 'sent');
      } finally { this.busy = false; }
    } catch (error) {
      this.diagnostic(stage, 'operation_failed', error);
      return response(409, 'unknown'); // Also fail closed for corrupt or inaccessible ledger.
    }
  }
}
