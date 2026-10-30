import { createServer } from 'node:http';
import { authenticated } from './core.mjs';

export function createProviderServer({ service, token, ready, session = null, adminToken = null }) {
  if (typeof token !== 'string' || token.length < 32) throw new Error('PROVIDER_TOKEN must have at least 32 characters');
  if (session && (typeof adminToken !== 'string' || adminToken.length < 32 || adminToken === token)) {
    throw new Error('Set a separate PROVIDER_ADMIN_TOKEN of at least 32 characters');
  }
  const server = createServer(async (req, res) => {
    const reply = (code, body) => {
      if (res.destroyed || res.writableEnded) return;
      res.writeHead(code, { 'Content-Type': 'application/json', 'Cache-Control': 'no-store' });
      res.end(JSON.stringify(body));
    };
    const management = req.url === '/session' || req.url?.startsWith('/session/');
    if (!authenticated(req.headers.authorization, management ? adminToken : token)) {
      reply(401, { status: 'unauthorized' }); req.resume(); return;
    }
    if (management && session) {
      req.resume();
      try {
        if (req.method === 'GET' && req.url === '/session') { reply(200, session.snapshot()); return; }
        const action = req.method === 'POST' && ({ '/session/connect': 'connect', '/session/disconnect': 'disconnect' })[req.url];
        if (action) {
          const result = await session[action](); reply(result.code, result.body); return;
        }
      } catch { reply(503, { status: 'error' }); return; }
      reply(404, { status: 'not_found' }); return;
    }
    if (req.method === 'GET' && req.url === '/health') {
      reply(200, { status: ready() ? 'ready' : 'not_ready' }); return;
    }
    if (req.method !== 'POST' || req.url !== '/messages') {
      reply(404, { status: 'not_found' }); req.resume(); return;
    }
    if (!(req.headers['content-type'] ?? '').toLowerCase().startsWith('application/json')) {
      reply(415, { status: 'json_required' }); req.resume(); return;
    }
    try {
      let size = 0;
      const parts = [];
      for await (const part of req) {
        size += part.length;
        if (size > 8192) { reply(413, { status: 'too_large' }); req.destroy(); return; }
        parts.push(part);
      }
      let input;
      try { input = JSON.parse(Buffer.concat(parts).toString('utf8')); }
      catch { reply(400, { status: 'invalid_json' }); return; }
      const result = await service.send(input);
      reply(result.code, result.body);
    } catch { reply(500, { status: 'unknown' }); }
  });
  server.requestTimeout = 15000;
  server.headersTimeout = 10000;
  return server;
}
