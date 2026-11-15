/**
 * Backward compatibility bridge for messageStore
 * Meneruskan ke store.js (implementasi sesuai referensi Cel)
 */
import { store, cacheMessage as storeCache, getMessageFromStoreById } from './store.js';

export const messageStore = store.messages;
export const cacheMessage = storeCache;
export const getMessage = getMessageFromStoreById;
