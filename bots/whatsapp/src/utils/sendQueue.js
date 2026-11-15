/**
 * Simple pass-through without deadlocking queue
 */
export async function enqueueSend(jid, sendFn) {
  return await sendFn();
}
