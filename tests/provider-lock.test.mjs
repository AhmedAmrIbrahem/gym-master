import test from 'node:test';
import assert from 'node:assert/strict';
import { mkdtemp, readFile, writeFile, rm, stat } from 'node:fs/promises';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { acquireProviderLock } from '../services/whatsapp-provider/provider-lock.mjs';

async function fixture(fn) {
  const dir = await mkdtemp(join(tmpdir(), 'gym-provider-lock-'));
  try { await fn(dir); } finally { await rm(dir, { recursive: true, force: true }); }
}

test('normal startup and graceful cleanup', async () => fixture(async dir => {
  const release = await acquireProviderLock(dir);
  assert.equal(JSON.parse(await readFile(join(dir, 'provider.lock'), 'utf8')).pid, process.pid);
  await release();
  await assert.rejects(stat(join(dir, 'provider.lock')), { code: 'ENOENT' });
}));

test('second provider cannot use same data dir', async () => fixture(async dir => {
  const release = await acquireProviderLock(dir);
  await assert.rejects(acquireProviderLock(dir), /already running/);
  await release();
}));

test('stale PID lock automatically recovered without touching session data', async () => fixture(async dir => {
  await writeFile(join(dir, 'provider.lock'), '9000000');
  const release = await acquireProviderLock(dir, { alive: () => false });
  assert.equal(JSON.parse(await readFile(join(dir, 'provider.lock'), 'utf8')).pid, process.pid);
  await release();
}));

test('malformed lock is preserved for safe manual investigation', async () => fixture(async dir => {
  await writeFile(join(dir, 'provider.lock'), 'corrupt');
  await assert.rejects(acquireProviderLock(dir), /safely verified/);
  assert.equal(await readFile(join(dir, 'provider.lock'), 'utf8'), 'corrupt');
}));

test('release does not delete a replacement lock', async () => fixture(async dir => {
  const release = await acquireProviderLock(dir);
  await writeFile(join(dir, 'provider.lock'), '999');
  await release();
  assert.equal(await readFile(join(dir, 'provider.lock'), 'utf8'), '999');
}));
