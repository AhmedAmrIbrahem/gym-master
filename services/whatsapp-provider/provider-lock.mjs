import { open, readFile, unlink, stat } from 'node:fs/promises';
import { randomUUID } from 'node:crypto';
import { join } from 'node:path';

const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

// Deliberately fail closed if ownership cannot be established.
export async function acquireProviderLock(directory, { pid = process.pid, alive = isProcessAlive } = {}) {
  const path = join(directory, 'provider.lock');
  const recoveryPath = join(directory, 'provider-recovery.lock');
  const owner = { pid, instance: randomUUID(), started: new Date().toISOString() };
  const payload = JSON.stringify(owner);

  // A recovery mutex prevents concurrent contenders from deleting one another's new lock.
  let recovery;
  for (let n = 0; n < 20; n++) {
    try { recovery = await open(recoveryPath, 'wx', 0o600); break; }
    catch (error) {
      if (error.code !== 'EEXIST') throw error;
      // Never steal a recovery mutex while another process may be examining the lock.
      // A recovery mutex contains a PID and is only reclaimed when that process is gone.
      let record;
      try { record = await readFile(recoveryPath, 'utf8'); }
      catch (readError) { if (readError.code === 'ENOENT') continue; throw readError; }
      const recoveryPid = Number(record.trim());
      if (Number.isSafeInteger(recoveryPid) && recoveryPid > 0 && !alive(recoveryPid)) {
        // A stale mutex can be removed. Recheck that its contents have not changed.
        if (await readFile(recoveryPath, 'utf8').catch(() => null) === record) {
          await unlink(recoveryPath).catch(error => { if (error.code !== 'ENOENT') throw error; });
        }
      } else if (record === '') {
        // A crash between exclusive creation and writing its PID leaves an empty mutex.
        // Only recover it after a grace period to avoid disrupting a concurrent writer.
        const info = await stat(recoveryPath).catch(() => null);
        if (info && Date.now() - info.mtimeMs > 30_000) {
          if (await readFile(recoveryPath, 'utf8').catch(() => null) === '') {
            await unlink(recoveryPath).catch(error => { if (error.code !== 'ENOENT') throw error; });
          }
        }
      }
      await delay(100);
    }
  }
  if (!recovery) throw new Error('Provider recovery is busy or locked. Check for another running provider.');

  try {
    await recovery.writeFile(String(pid));
    await recovery.sync();
    // The recovery mutex serializes stale-lock detection and creation.
    for (let n = 0; n < 2; n++) {
      let handle;
      try {
        handle = await open(path, 'wx', 0o600);
        await handle.writeFile(payload);
        await handle.sync();
        await handle.close();
        handle = undefined;
        return async () => {
          // Never remove a replacement lock belonging to another process.
          const current = await readFile(path, 'utf8').catch(() => null);
          if (current === payload) await unlink(path).catch(error => { if (error.code !== 'ENOENT') throw error; });
        };
      } catch (error) {
        await handle?.close().catch(() => {});
        if (error.code !== 'EEXIST') throw error;
        const current = await readFile(path, 'utf8').catch(() => null);
        const oldPid = (() => { try { const record = JSON.parse(current); return typeof record === 'number' ? record : record?.pid; } catch { return Number(current?.trim()); } })();
        if (!Number.isSafeInteger(oldPid) || oldPid <= 0 || alive(oldPid)) {
          throw new Error('Provider already running, or its lock cannot be safely verified.');
        }
        // pid no longer exists. Preserve session and ledger; clear only stale process lock.
        const again = await readFile(path, 'utf8').catch(() => null);
        if (current !== again) throw new Error('Provider lock changed during recovery; retry startup.');
        await unlink(path);
        console.log('Recovered stale provider lock from stopped process.');
      }
    }
    throw new Error('Unable to acquire provider lock.');
  } finally {
    await recovery.close().catch(() => {});
    await unlink(recoveryPath).catch(() => {});
  }
}

function isProcessAlive(pid) {
  try { process.kill(pid, 0); return true; }
  catch (error) {
    if (error.code === 'ESRCH') return false;
    return true; // EPERM or other errors: treat as alive, never steal lock.
  }
}
