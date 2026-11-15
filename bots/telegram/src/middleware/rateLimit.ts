import type { Context, MiddlewareFn } from 'telegraf';
import { logger } from '../utils/logger.js';

interface RateLimitConfig {
    /** Time window in seconds */
    window: number;
    /** Max messages allowed per window */
    limit: number;
    /** Reply message when rate limited */
    message: string;
}

const DEFAULT_CONFIG: RateLimitConfig = {
    window: 10,
    limit: 5,
    message: '⏳ Terlalu banyak request. Silakan tunggu beberapa detik.',
};

const userHits = new Map<number, number[]>();

// Clean up stale entries every 60 seconds to prevent memory leaks
setInterval(() => {
    const now = Date.now();
    for (const [userId, timestamps] of userHits) {
        const filtered = timestamps.filter((t) => now - t < 60_000);
        if (filtered.length === 0) {
            userHits.delete(userId);
        } else {
            userHits.set(userId, filtered);
        }
    }
}, 60_000);

export const rateLimit = (config: Partial<RateLimitConfig> = {}): MiddlewareFn<Context> => {
    const { window, limit, message } = { ...DEFAULT_CONFIG, ...config };
    const windowMs = window * 1000;

    return async (ctx, next) => {
        const userId = ctx.from?.id;
        if (!userId) return next();

        const now = Date.now();
        const timestamps = userHits.get(userId) || [];
        const recentHits = timestamps.filter((t) => now - t < windowMs);

        if (recentHits.length >= limit) {
            logger.warn('rateLimit', `User ${userId} rate limited (${recentHits.length}/${limit})`);
            if (ctx.callbackQuery) {
                await ctx.answerCbQuery(message, { show_alert: true }).catch(() => {});
            } else {
                await ctx.reply(message).catch(() => {});
            }
            return;
        }

        recentHits.push(now);
        userHits.set(userId, recentHits);
        return next();
    };
};
