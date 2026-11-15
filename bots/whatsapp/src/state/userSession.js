/**
 * In-memory conversational state management for WhatsApp users
 */
const sessions = new Map();
const SESSION_TTL_MS = 10 * 60 * 1000; // 10 minutes

export const userSession = {
  get(jid) {
    const session = sessions.get(jid);
    if (!session) return null;

    if (Date.now() - session.updatedAt > SESSION_TTL_MS) {
      sessions.delete(jid);
      return null;
    }
    return session;
  },

  set(jid, state, data = {}) {
    sessions.set(jid, {
      state,
      data,
      updatedAt: Date.now()
    });
  },

  updateData(jid, newData = {}) {
    const session = this.get(jid);
    if (!session) return;
    session.data = { ...session.data, ...newData };
    session.updatedAt = Date.now();
    sessions.set(jid, session);
  },

  clear(jid) {
    sessions.delete(jid);
  }
};
