// QR/session data lives only in memory and is exposed exclusively via the management API.
export class WhatsAppSession {
  constructor({ createClient, encodeQr, isSending = () => false, now = () => Date.now() }) {
    Object.assign(this, { createClient, encodeQr, isSending, now });
    this.client = null;
    this.status = 'idle';
    this.changing = false;
    this.qrVersion = 0;
    this.clearPrivateState();
  }
  clearPrivateState() {
    this.qrVersion++;
    this.qrDataUrl = null;
    this.qrExpiresAt = 0;
    this.senderPhone = null;
  }
  isReady() { return this.status === 'ready' && !this.changing && this.client !== null; }
  snapshot() {
    return {
      status: this.status,
      sender_phone: this.isReady() ? this.senderPhone : null,
      qr_data_url: this.status === 'qr' && this.qrExpiresAt > this.now() ? this.qrDataUrl : null,
    };
  }
  async connect() {
    if (this.changing || this.isSending()) return { code: 409, body: { status: 'busy' } };
    if (['starting', 'qr', 'ready'].includes(this.status)) return { code: 200, body: this.snapshot() };
    this.changing = true;
    this.clearPrivateState();
    const oldClient = this.client;
    this.client = null;
    this.status = 'starting';
    try {
      if (oldClient) await oldClient.destroy();
      const client = this.createClient();
      this.client = client;
      client.on('qr', async qr => {
        if (this.client !== client) return;
        this.clearPrivateState();
        this.status = 'qr';
        const version = this.qrVersion;
        try {
          const image = await this.encodeQr(qr);
          if (this.client === client && this.status === 'qr' && this.qrVersion === version) {
            this.qrDataUrl = image;
            this.qrExpiresAt = this.now() + 60000;
          }
        } catch {
          if (this.client === client && this.qrVersion === version) {
            this.clearPrivateState(); this.status = 'error';
          }
        }
      });
      client.on('ready', () => {
        if (this.client !== client) return;
        this.clearPrivateState();
        const phone = String(client.info?.wid?.user ?? '');
        this.senderPhone = /^[1-9][0-9]{7,14}$/.test(phone) ? `+${phone}` : null;
        this.status = 'ready';
      });
      for (const event of ['disconnected', 'auth_failure']) {
        client.on(event, () => {
          if (this.client === client) { this.clearPrivateState(); this.status = event; }
        });
      }
      // Browser startup can outlast an HTTP request. Poll status instead of holding it open.
      Promise.resolve().then(() => this.client === client ? client.initialize() : undefined).catch(() => {
        if (this.client === client) { this.clearPrivateState(); this.status = 'error'; }
      });
      return { code: 202, body: this.snapshot() };
    } catch {
      this.client = oldClient; // Keep failed cleanup visible; never create a second browser over it.
      this.status = 'error';
      return { code: 503, body: this.snapshot() };
    } finally { this.changing = false; }
  }
  async disconnect() {
    if (this.changing || this.isSending() || this.status === 'starting') {
      return { code: 409, body: { status: 'busy' } };
    }
    const client = this.client, wasReady = this.isReady();
    this.changing = true;
    this.client = null; // Ignore all late QR/ready events from the old browser.
    this.clearPrivateState();
    this.status = 'disconnecting';
    try {
      if (client) {
        if (wasReady) {
          // Official library method revokes the linked-device session and removes its LocalAuth.
          await client.logout();
        } else {
          await client.destroy();
          await client.authStrategy.logout();
        }
      }
      this.status = 'disconnected';
      return { code: 200, body: this.snapshot() };
    } catch {
      this.client = client;
      this.status = 'error';
      return { code: 503, body: this.snapshot() };
    } finally { this.changing = false; }
  }
  async getNumberId(phone) {
    if (!this.isReady()) throw new Error('not_ready');
    return this.client.getNumberId(phone);
  }
  async sendMessage(...args) {
    if (!this.isReady()) throw new Error('not_ready');
    return this.client.sendMessage(...args);
  }
  async close() {
    this.changing = true;
    const client = this.client;
    this.client = null;
    this.clearPrivateState();
    this.status = 'disconnected';
    if (client) await client.destroy(); // Service shutdown preserves LocalAuth for next start.
  }
}
