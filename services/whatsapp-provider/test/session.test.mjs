import { test } from 'node:test';
import assert from 'node:assert/strict';
import { EventEmitter } from 'node:events';
import { Readable } from 'node:stream';
import { WhatsAppSession } from '../session.mjs';
import { createProviderServer } from '../http.mjs';

const tick = () => new Promise(resolve => setImmediate(resolve));
class FakeClient extends EventEmitter {
  constructor() {
    super(); this.info = { wid: { user: '201012345678' } }; this.destroyed = 0; this.loggedOut = 0; this.started = 0;
    this.authStrategy = { logout: async () => { this.authRemoved = true; } };
  }
  async initialize() { this.started++; }
  async destroy() { this.destroyed++; }
  async logout() { this.loggedOut++; }
  async getNumberId(phone) { return { _serialized: phone + '@c.us' }; }
  async sendMessage() { return { id: 'ok' }; }
}
function fixture(overrides = {}) {
  const clients = [];
  const session = new WhatsAppSession({
    createClient: () => { const client = new FakeClient(); clients.push(client); return client; },
    encodeQr: async () => 'data:image/png;base64,cXI=', ...overrides,
  });
  return { session, clients };
}
test('first-time session stays idle until explicit admin connect', async () => {
  const { session, clients } = fixture();
  assert.deepEqual(session.snapshot(), { status: 'idle', sender_phone: null, qr_data_url: null });
  assert.equal(clients.length, 0);
  assert.equal((await session.connect()).code, 202);
  await tick();
  assert.equal(clients[0].started, 1);
  await session.connect();
  assert.equal(clients.length, 1);
});
test('QR expires and ready event clears it and shows actual linked account', async () => {
  let time = 0;
  const { session, clients } = fixture({ now: () => time });
  await session.connect();
  clients[0].emit('qr', 'raw-secret'); await tick();
  assert.equal(session.snapshot().qr_data_url, 'data:image/png;base64,cXI=');
  time = 60001;
  assert.equal(session.snapshot().qr_data_url, null);
  clients[0].emit('ready');
  assert.deepEqual(session.snapshot(), { status: 'ready', sender_phone: '+201012345678', qr_data_url: null });
  assert.equal(session.isReady(), true);
});
test('stale asynchronous QR completion never overwrites connected state', async () => {
  let finish;
  const { session, clients } = fixture({ encodeQr: () => new Promise(resolve => { finish = resolve; }) });
  await session.connect(); clients[0].emit('qr', 'raw-secret');
  clients[0].emit('ready'); finish('data:image/png;base64,cXI='); await tick();
  assert.equal(session.snapshot().qr_data_url, null);
  assert.equal(session.status, 'ready');
});
test('admin disconnect revokes old sender; reconnect uses a new account and ignores old events', async () => {
  const { session, clients } = fixture();
  await session.connect(); clients[0].emit('ready');
  assert.equal((await session.disconnect()).code, 200);
  assert.equal(clients[0].loggedOut, 1);
  assert.equal(session.isReady(), false);
  assert.equal(session.snapshot().sender_phone, null);
  await session.connect(); clients[1].info.wid.user = '201112345678'; clients[1].emit('ready');
  clients[0].emit('qr', 'old'); clients[0].emit('disconnected'); await tick();
  assert.equal(session.snapshot().sender_phone, '+201112345678');
  assert.equal(session.isReady(), true);
});
test('disconnecting an unpaired QR closes browser and removes LocalAuth', async () => {
  const { session, clients } = fixture();
  await session.connect(); clients[0].emit('qr', 'secret'); await tick();
  await session.disconnect();
  assert.equal(clients[0].destroyed, 1);
  assert.equal(clients[0].authRemoved, true);
  assert.equal(session.status, 'disconnected');
});
test('cannot change sender while a send or startup is active', async () => {
  let sending = false;
  const { session, clients } = fixture({ isSending: () => sending });
  await session.connect();
  assert.equal((await session.disconnect()).code, 409);
  clients[0].emit('ready'); sending = true;
  assert.equal((await session.disconnect()).code, 409);
  assert.equal((await session.connect()).code, 409);
  assert.equal(session.isReady(), true);
});
test('session disconnect/auth failure clears QR/number and blocks all sends', async () => {
  const { session, clients } = fixture();
  await session.connect(); clients[0].emit('ready'); clients[0].emit('auth_failure');
  assert.equal(session.isReady(), false);
  assert.equal(session.snapshot().sender_phone, null);
  await assert.rejects(session.sendMessage('x', 'text'), /not_ready/);
  await assert.rejects(session.getNumberId('201012345678'), /not_ready/);
});
test('failed logout never reports success or leaves sending enabled', async () => {
  const { session, clients } = fixture();
  await session.connect(); clients[0].emit('ready');
  clients[0].logout = async () => { throw new Error('private detail'); };
  assert.equal((await session.disconnect()).code, 503);
  assert.equal(session.status, 'error'); assert.equal(session.isReady(), false);
});
test('service shutdown closes browser but preserves authorization for restart', async () => {
  const { session, clients } = fixture();
  await session.connect(); clients[0].emit('ready'); await session.close();
  assert.equal(clients[0].destroyed, 1); assert.equal(clients[0].loggedOut, 0);
  assert.equal(session.snapshot().sender_phone, null);
});
test('management HTTP API requires its separate credential, hides QR from sending token, and restricts verbs', async () => {
  const { session, clients } = fixture(), token = 's'.repeat(32), adminToken = 'm'.repeat(32);
  const server = createProviderServer({ service: { send: async () => ({ code: 200, body: { status: 'sent' } }) }, token, adminToken, session, ready: () => session.isReady() });
  async function request(method, url, credential = token) {
    return new Promise(resolve => {
      const req = Readable.from([]); req.method = method; req.url = url; req.headers = { authorization: `Bearer ${credential}` };
      const res = { writeHead(code) { this.code = code; }, end(data) { resolve({ code: this.code, body: JSON.parse(data) }); } };
      server.emit('request', req, res);
    });
  }
  assert.equal((await request('POST', '/session/connect')).code, 401);
  assert.equal(clients.length, 0);
  assert.equal((await request('GET', '/session', adminToken)).body.status, 'idle');
  assert.equal((await request('POST', '/session/connect', adminToken)).code, 202);
  clients[0].emit('qr', 'private'); await tick();
  assert.equal((await request('GET', '/session')).code, 401);
  assert.equal((await request('GET', '/health')).body.qr_data_url, undefined);
  assert.equal((await request('GET', '/session', adminToken)).body.qr_data_url, 'data:image/png;base64,cXI=');
  assert.equal((await request('GET', '/session/disconnect', adminToken)).code, 404);
  assert.equal((await request('POST', '/messages', adminToken)).code, 401);
  assert.equal((await request('POST', '/session/disconnect', adminToken)).code, 200);
});
test('management credential cannot be missing, weak or reused', () => {
  const { session } = fixture();
  for (const adminToken of [null, 'weak', 's'.repeat(32)]) {
    assert.throws(() => createProviderServer({ service: {}, token: 's'.repeat(32), ready: () => false, session, adminToken }), /separate/);
  }
});
