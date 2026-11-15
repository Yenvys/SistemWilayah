import { Context } from 'telegraf';
import { logger } from '../utils/logger.js';

export const pingCommand = async (ctx: Context) => {
    try {
        if (!ctx.chat) return;
        
        const start = Date.now();
        const message = await ctx.reply('Pong! 🏓');
        const latency = Date.now() - start;

        await ctx.telegram.editMessageText(
            ctx.chat.id, 
            message.message_id, 
            undefined, 
            `Pong! 🏓 Latency: ${latency}ms`
        );

        logger.info('pingCommand', `Latency: ${latency}ms`);
    } catch (error) {
        logger.error('pingCommand', 'Failed to check latency', error);
        await ctx.reply('⚠️ Gagal mengecek latency.').catch(() => {});
    }
};
