import { Context, Markup } from 'telegraf';
import axios from 'axios';
import { logger } from '../utils/logger.js';

export const loginCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    // @ts-ignore - message text exists on text messages
    const text = ctx.message?.text || '';
    const args = text.split(' ').slice(1);

    if (args.length < 2) {
        return ctx.reply(
            '⚠️ <b>Format login salah.</b>\n\n' +
            'Gunakan:\n<code>/login &lt;NIK/Email/No.HP&gt; &lt;Password&gt;</code>\n\n' +
            '<i>Contoh: /login 3201234567890001 password123</i>',
            { parse_mode: 'HTML' }
        );
    }

    const loginInput = args[0];
    const password = args.slice(1).join(' '); // in case password has spaces

    try {
        const loadingMsg = await ctx.reply('Memverifikasi kredensial Anda, mohon tunggu...');

        const response = await axios.post('http://siwil.test/api/telegram/login', {
            login: loginInput,
            password: password,
            telegram_id: telegramId.toString()
        });

        if (response.data && response.data.message) {
            const loginSuccessKeyboard = Markup.inlineKeyboard([
                [
                    Markup.button.callback('🏛️ Layanan Desa', 'action_layanan')
                ],
                [
                    Markup.button.callback('📋 Menu Utama', 'action_show_menu')
                ]
            ]);

            await ctx.telegram.editMessageText(
                telegramId,
                loadingMsg.message_id,
                undefined,
                `✅ <b>${response.data.message}</b>\n\n` +
                `Halo, <b>${response.data.user.name}</b> (Role: <i>${response.data.user.role}</i>)!\n` +
                `Akun Anda berhasil terhubung dengan sistem SIWIL.\n\n` +
                `Silakan klik tombol <b>🏛️ Layanan Desa</b> di bawah untuk melihat pilihan layanan surat dan pengaduan:`,
                {
                    parse_mode: 'HTML',
                    ...loginSuccessKeyboard
                }
            );
            
            // Delete the message containing the password for security
            try {
                if (ctx.message?.message_id) {
                    await ctx.deleteMessage(ctx.message.message_id);
                }
            } catch (e) {
                // Ignore if bot doesn't have permission to delete message
            }
        }
    } catch (error: any) {
        const errorMsg = error.response?.data?.error || 'Terjadi kesalahan saat menghubungi server.';
        await ctx.reply(`❌ Login Gagal: ${errorMsg}`);
    }
};
