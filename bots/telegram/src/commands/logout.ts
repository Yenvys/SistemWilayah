import { Context, Markup } from 'telegraf';
import axios from 'axios';
import { logger } from '../utils/logger.js';

export const logoutCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    try {
        const loadingMsg = await ctx.reply('Memproses logout, mohon tunggu...');

        const response = await axios.post('http://siwil.test/api/telegram/logout', {
            telegram_id: telegramId.toString()
        });

        if (response.data && response.data.message) {
            const logoutKeyboard = Markup.inlineKeyboard([
                [Markup.button.callback('📋 Menu Utama', 'action_show_menu')]
            ]);

            await ctx.telegram.editMessageText(
                telegramId,
                loadingMsg.message_id,
                undefined,
                `✅ <b>${response.data.message}</b>\n\nUntuk login kembali dengan akun SIWIL, ketik:\n<code>/login &lt;NIK/Email/No.HP&gt; &lt;Password&gt;</code>`,
                { parse_mode: 'HTML', ...logoutKeyboard }
            );
        }
    } catch (error: any) {
        const errorMsg = error.response?.data?.error || 'Terjadi kesalahan saat menghubungi server.';
        logger.error('logoutCommand', 'Error logging out', error.response?.data || error.message);
        await ctx.reply(`❌ Logout Gagal: ${errorMsg}`);
    }
};
