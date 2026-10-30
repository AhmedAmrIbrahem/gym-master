import { test } from 'node:test';
import assert from 'node:assert/strict';
import { mkdtemp, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { once } from 'node:events';
import { Readable } from 'node:stream';
import { authenticated, Ledger, MessageService } from '../core.mjs';
import { createProviderServer } from '../http.mjs';

const input = { idempotency_key: '8fd83ffe-20f2-48b8-ade2-117e8d998f63', phone: '+201012345678', text: 'تذكير الجيم' };
async function fixture(t, overrides = {}) {
  const dir = await mkdtemp(join(tmpdir(), 'gym-provider-'));
  t.after(() => rm(dir, { recursive: true, force: true }));
  let calls = 0;
  const client = { getNumberId: async () => ({ _serialized: 'private@c.us' }), sendMessage: async () => { calls++; return { id: 'ok' }; }, ...overrides };
  const ledger = new Ledger(dir);
  return { dir, ledger, client, calls: () => calls, service: new MessageService({ ledger, client, ready: () => true, report: () => {} }) };
}
test('requires a strong matching bearer token', () => {
  assert.equal(authenticated('Bearer abc', 'abc'), false);
  assert.equal(authenticated(`Bearer ${'a'.repeat(32)}`, 'a'.repeat(32)), true);
  assert.equal(authenticated(`Bearer ${'b'.repeat(32)}`, 'a'.repeat(32)), false);
});
test('durable deduplication survives service restart and rejects changed payload', async t => {
  const f = await fixture(t);
  assert.equal((await f.service.send(input)).body.status, 'sent');
  const restarted = new MessageService({ ledger: new Ledger(f.dir), client: f.client, ready: () => true });
  assert.equal((await restarted.send(input)).body.status, 'sent');
  assert.equal((await restarted.send({ ...input, text: 'different' })).code, 409);
  assert.equal(f.calls(), 1);
});
test('concurrent duplicate requests cannot send twice', async t => {
  const f = await fixture(t);
  await Promise.all([f.service.send(input), f.service.send(input)]);
  assert.equal(f.calls(), 1);
});
test('exclusive ledger reservation protects separate service instances', async t => {
  const f = await fixture(t);
  const second = new MessageService({ ledger: new Ledger(f.dir), client: f.client, ready: () => true });
  await Promise.all([f.service.send(input), second.send(input)]);
  assert.equal(f.calls(), 1);
});
test('ambiguous send failure is never replayed', async t => {
  let attempts = 0;
  const f = await fixture(t, { sendMessage: async () => { attempts++; throw new Error('connection lost after send'); } });
  assert.equal((await f.service.send(input)).body.status, 'unknown');
  assert.equal((await f.service.send(input)).body.status, 'unknown');
  assert.equal(attempts, 1);
});
test('requests send confirmation and leaves a missing return unknown without replay', async t => {
  let attempts = 0;
  const diagnostics = [];
  const f = await fixture(t, { sendMessage: async (to, text, options) => {
    attempts++;
    assert.equal(options.waitUntilMsgSent, true);
    assert.equal(options.sendSeen, false);
    return undefined;
  } });
  f.service.report = detail => diagnostics.push(detail);
  assert.equal((await f.service.send(input)).body.status, 'unknown');
  assert.equal(diagnostics[0].stage, 'whatsapp_send');
  assert.equal(diagnostics[0].reason, 'send_unconfirmed');
  await f.service.send(input);
  assert.equal(attempts, 1);
});
test('diagnostics identify a stage without exposing raw error secrets or message content', async t => {
  const diagnostics = [];
  const f = await fixture(t, { sendMessage: async () => { throw new Error(`Cannot serialize _serialized private-token ${input.phone} ${input.text}`); } });
  f.service.report = detail => diagnostics.push(detail);
  await f.service.send(input);
  assert.equal(diagnostics[0].hint, 'message_serialization');
  const output = JSON.stringify(diagnostics);
  assert.equal(output.includes('private-token'), false);
  assert.equal(output.includes(input.phone), false);
  assert.equal(output.includes(input.text), false);
});
test('timeout leaves a durable unknown record', async t => {
  const f = await fixture(t, { sendMessage: () => new Promise(() => {}) });
  f.service.timeoutMs = 10;
  assert.equal((await f.service.send(input)).body.status, 'unknown');
  assert.equal((await f.ledger.get(input.idempotency_key)).status, 'unknown');
  assert.equal(f.service.activeOperations, 1);
  assert.equal((await f.service.send({ ...input, idempotency_key: '9fd83ffe-20f2-48b8-ade2-117e8d998f63' })).code, 503);
});
test('disconnected provider can retry without reserving a key', async t => {
  const f = await fixture(t);
  f.service.ready = () => false;
  assert.equal((await f.service.send(input)).code, 503);
  assert.equal(await f.ledger.get(input.idempotency_key), null);
  f.service.ready = () => true;
  assert.equal((await f.service.send(input)).body.status, 'sent');
});
test('rejects invalid numbers and group destinations', async t => {
  const f = await fixture(t);
  for (const phone of ['123@g.us', '01012345678', '+20bad']) {
    assert.equal((await f.service.send({ ...input, phone })).code, 400);
  }
  const g = await fixture(t, { getNumberId: async () => null });
  assert.equal((await g.service.send(input)).code, 422);
  assert.equal(g.calls(), 0);
});
test('corrupted ledger fails closed', async t => {
  const f = await fixture(t);
  await writeFile(f.ledger.path(input.idempotency_key), 'bad');
  assert.equal((await f.service.send(input)).body.status, 'unknown');
  assert.equal(f.calls(), 0);
});
test('HTTP request handler validates auth, health, JSON and duplicate POST without sockets', async t => {
  const f = await fixture(t), token = 'a'.repeat(32);
  const server = createProviderServer({ service: f.service, token, ready: () => true });
  async function request(method, url, body = '', authorized = true) {
    return new Promise(resolve => {
      const req = Readable.from([Buffer.from(body)]);
      req.method = method; req.url = url;
      req.headers = { 'content-type': 'application/json', ...(authorized ? { authorization: `Bearer ${token}` } : {}) };
      const res = { writeHead(code) { this.code = code; }, end(data) { resolve({ code: this.code, body: JSON.parse(data) }); } };
      server.emit('request', req, res);
    });
  }
  assert.equal((await request('GET', '/health', '', false)).code, 401);
  assert.equal((await request('GET', '/health')).body.status, 'ready');
  assert.equal((await request('POST', '/messages', '{')).code, 400);
  assert.equal((await request('POST', '/messages', JSON.stringify({}))).code, 400);
  assert.equal((await request('POST', '/messages', 'x'.repeat(9000))).code, 413);
  for (let i = 0; i < 2; i++) {
    assert.equal((await request('POST', '/messages', JSON.stringify(input))).body.status, 'sent');
  }
  assert.equal(f.calls(), 1);
});
test('HTTP loopback integration', async t => {
  const f = await fixture(t), token = 'a'.repeat(32);
  const server = createProviderServer({ service: f.service, token, ready: () => true });
  server.listen(0, '127.0.0.1');
  await once(server, 'listening');
  t.after(() => { server.closeAllConnections(); server.close(); });
  const url = `http://127.0.0.1:${server.address().port}`;
  const headers = { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' };
  let unauthorized;
  try { unauthorized = await fetch(`${url}/health`); }
  catch (error) {
    if (['EACCES', 'EPERM'].includes(error.cause?.code)) {
      t.skip('OS sandbox denies loopback HTTP connections; request handler covered separately'); return;
    }
    throw error;
  }
  assert.equal(unauthorized.status, 401);
  assert.equal((await (await fetch(`${url}/health`, { headers })).json()).status, 'ready');
  assert.equal((await fetch(`${url}/messages`, { method: 'POST', headers, body: '{' })).status, 400);
  for (let i = 0; i < 2; i++) {
    assert.equal((await fetch(`${url}/messages`, { method: 'POST', headers, body: JSON.stringify(input) })).status, 200);
  }
  assert.equal(f.calls(), 1);
});
