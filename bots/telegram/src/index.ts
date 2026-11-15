import { Telegraf, Markup } from 'telegraf';
import * as dotenv from 'dotenv';
import axios from 'axios';
import { logger } from './utils/logger.js';
import { rateLimit } from './middleware/rateLimit.js';
import { pingCommand } from './commands/ping.js';
import { infoCommand } from './commands/info.js';
import { menuCommand, menuMessage, menuKeyboard } from './commands/menu.js';
import { stickerCommand, stickerUsageMessage, handleMediaMessage, togifCommand } from './commands/sticker.js';
import { songCommand, songUsageMessage } from './commands/song.js';
import {
    layananCommand,
    pengajuanCommand,
    cekPengajuanCommand,
    pengaduanCommand,
    cekPengaduanCommand,
    pengajuanMenuCommand,
    pengaduanMenuCommand,
    pilihSuratHandler,
    batalPengajuanHandler,
    downloadSuratHandler,
    handleLayananSessionText
} from './commands/layanan.js';
import { loginCommand } from './commands/login.js';
import { logoutCommand } from './commands/logout.js';

dotenv.config();

const token = process.env.BOT_TOKEN;
if (!token || token === 'your_token_here') {
    throw new Error('BOT_TOKEN must be provided in the .env file!');
}

const bot = new Telegraf(token);

// --- Middleware ---
bot.use(rateLimit({ window: 10, limit: 5 }));

// --- Global Error Handler ---
bot.catch((err, ctx) => {
    logger.error('bot.catch', `Error for ${ctx.updateType}`, err);
    ctx.reply('⚠️ Terjadi kesalahan. Silakan coba lagi.').catch(() => {});
});

// --- Commands ---
bot.start(async (ctx) => {
    const payload = ctx.payload;
    if (payload) {
        // Handle deep link connection
        try {
            const loadingMsg = await ctx.reply('Menghubungkan akun Anda, mohon tunggu...');
            
            const response = await axios.post('http://siwil.test/api/telegram/connect', {
                token: payload,
                telegram_id: ctx.chat.id.toString()
            });

            if (response.data && response.data.message) {
                const connectedKeyboard = Markup.inlineKeyboard([
                    [Markup.button.callback('🏛️ Layanan Desa', 'action_layanan')],
                    [Markup.button.callback('📋 Menu Utama', 'action_show_menu')]
                ]);

                await ctx.telegram.editMessageText(
                    ctx.chat.id,
                    loadingMsg.message_id,
                    undefined,
                    `✅ <b>${response.data.message}</b>\n\nSelamat datang, <b>${response.data.user.name}</b>!\nAkun Anda telah berhasil terhubung ke portal layanan desa.`,
                    { parse_mode: 'HTML', ...connectedKeyboard }
                );
            }
        } catch (error: any) {
            logger.error('bot.start', 'Error connecting telegram', error.response?.data || error.message);
            await ctx.reply('❌ Gagal menghubungkan akun. Token mungkin tidak valid atau sudah kadaluarsa.');
        }
        return;
    }

    const startKeyboard = Markup.inlineKeyboard([
        [Markup.button.callback('🏛️ Layanan Desa', 'action_layanan')],
        [Markup.button.callback('📋 Menu Utama', 'action_show_menu')]
    ]);
    
    await ctx.reply(
        `Halo! Saya Celt, Bot Pelayanan Warga & Utilitas Terpadu (SIWIL).\n\n` +
        `Gunakan tombol <b>Layanan Desa</b> di bawah untuk mengakses pengajuan surat, cek dokumen, dan download surat resmi.`,
        { parse_mode: 'HTML', ...startKeyboard }
    );
});

bot.help(menuCommand);
bot.command('menu', menuCommand);
bot.command('ping', pingCommand);
bot.command('info', infoCommand);
bot.command('s', stickerCommand);
bot.command('song', songCommand);
bot.command('togif', togifCommand);
bot.command('layanan', layananCommand);
bot.command('login', loginCommand);
bot.command('logout', logoutCommand);
bot.command('batal', batalPengajuanHandler);
bot.command('pengajuan', pengajuanCommand);
bot.command('cek_pengajuan', cekPengajuanCommand);
bot.command('pengaduan', pengaduanCommand);
bot.command('cek_pengaduan', cekPengaduanCommand);
bot.command('download_surat', async (ctx) => {
    const message = ctx.message;
    const text = (message && 'text' in message) ? message.text : '';
    const args = text.split(' ').slice(1);
    if (args.length === 0 || !args[0]) {
        return ctx.reply('⚠️ Harap masukkan nomor pengajuan surat.\nContoh: <code>/download_surat SRT-20261016-M77S</code>', {
            parse_mode: 'HTML'
        });
    }
    return downloadSuratHandler(ctx, args[0].trim());
});

// --- Inline Button Actions ---
bot.action('action_layanan', async (ctx) => {
    await ctx.answerCbQuery();
    return layananCommand(ctx);
});

bot.action('action_layanan_pengajuan_menu', async (ctx) => {
    await ctx.answerCbQuery();
    return pengajuanMenuCommand(ctx);
});

bot.action('action_layanan_cek_pengajuan', async (ctx) => {
    await ctx.answerCbQuery();
    return cekPengajuanCommand(ctx);
});

bot.action('action_layanan_pengaduan_menu', async (ctx) => {
    await ctx.answerCbQuery();
    return pengaduanMenuCommand(ctx);
});

bot.action('action_layanan_cek_pengaduan', async (ctx) => {
    await ctx.answerCbQuery();
    return cekPengaduanCommand(ctx);
});

bot.action('action_logout_confirm', async (ctx) => {
    await ctx.answerCbQuery();
    return logoutCommand(ctx);
});

bot.action('action_batal_pengajuan', async (ctx) => {
    await ctx.answerCbQuery('Pengajuan dibatalkan');
    return batalPengajuanHandler(ctx);
});

bot.action(/^action_pilih_surat_(.+)$/, async (ctx) => {
    await ctx.answerCbQuery();
    const shortCode = ctx.match[1];
    if (shortCode) {
        return pilihSuratHandler(ctx, shortCode);
    }
});

bot.action(/^action_dl_(.+)$/, async (ctx) => {
    await ctx.answerCbQuery('Memproses download PDF...');
    const requestNumber = ctx.match[1];
    if (requestNumber) {
        return downloadSuratHandler(ctx, requestNumber);
    }
});

bot.action('action_ping', async (ctx) => {
    await ctx.answerCbQuery();
    return pingCommand(ctx);
});

bot.action('action_info', async (ctx) => {
    await ctx.answerCbQuery();
    return infoCommand(ctx);
});

bot.action('action_show_menu', async (ctx) => {
    await ctx.answerCbQuery();
    await ctx.editMessageText(menuMessage, { 
        parse_mode: 'HTML', 
        ...menuKeyboard 
    });
});

bot.action('action_sticker', async (ctx) => {
    await ctx.answerCbQuery();
    await ctx.editMessageText(stickerUsageMessage, {
        parse_mode: 'HTML',
        ...Markup.inlineKeyboard([
            Markup.button.callback('⬅️ Kembali ke Menu', 'action_show_menu')
        ])
    });
});

bot.action('action_song', async (ctx) => {
    await ctx.answerCbQuery();
    await ctx.editMessageText(songUsageMessage, {
        parse_mode: 'HTML',
        ...Markup.inlineKeyboard([
            Markup.button.callback('⬅️ Kembali ke Menu', 'action_show_menu')
        ])
    });
});

// --- Media Handlers (auto-convert to sticker) ---
bot.on('photo', handleMediaMessage);
bot.on('video', handleMediaMessage);
bot.on('animation', handleMediaMessage);
bot.on('document', handleMediaMessage);

// --- Text Handler (handles active sessions first, then fallback) ---
bot.on('text', async (ctx) => {
    const isHandled = await handleLayananSessionText(ctx);
    if (isHandled) return;

    await ctx.reply(
        '❓ Perintah tidak dikenali.\nKetik /layanan untuk menu layanan atau /menu untuk daftar perintah.'
    );
});

// --- Launch ---
bot.launch().then(() => {
    bot.telegram.setMyCommands([
        { command: 'layanan', description: '🏛️ Portal menu layanan desa (SIWIL)' },
        { command: 'cek_pengajuan', description: '📑 Cek status & unduh surat PDF' },
        { command: 'pengajuan', description: '📄 Ajukan surat keterangan baru' },
        { command: 'pengaduan', description: '📢 Buat pengaduan warga' },
        { command: 'cek_pengaduan', description: '📋 Cek riwayat pengaduan' },
        { command: 'login', description: '🔑 Login ke akun desa (SIWIL)' },
        { command: 'logout', description: '🚪 Logout dari akun desa' },
        { command: 'menu', description: '📋 Tampilkan menu utama' },
        { command: 'ping', description: '🏓 Cek latensi bot' },
        { command: 'info', description: 'ℹ️ Info bot dan server' },
        { command: 'song', description: '🎵 Download lagu' },
        { command: 's', description: '🖼️ Buat stiker' }
    ]);
    logger.info('bot', 'Bot is running and commands are registered.');
});

// Enable graceful stop
process.once('SIGINT', () => bot.stop('SIGINT'));
process.once('SIGTERM', () => bot.stop('SIGTERM'));
