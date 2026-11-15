import { Context, Markup } from 'telegraf';
import { logger } from '../utils/logger.js';

export const menuMessage = `<b>Celt Menu</b>
Pilih menu di bawah ini atau gunakan perintah teks:

🏛️ <b>Layanan Warga (SIWIL)</b>
• <code>/layanan</code> - Menu portal layanan desa
• <code>/login &lt;NIK/Email&gt; &lt;Password&gt;</code> - Masuk ke akun desa
• <code>/cek_pengajuan</code> - Cek status & download surat PDF
• <code>/pengajuan [Kode] [Keperluan]</code> - Ajukan surat
• <code>/pengaduan [Judul] [Keterangan]</code> - Buat pengaduan

🎵 <b>Song Downloader</b>
• <code>/song &lt;judul lagu&gt;</code>
• <code>/song &lt;url youtube&gt;</code>

🖼️ <b>Sticker Maker</b>
• Reply foto/video/GIF dengan <code>/s</code>
• Atau kirim media langsung ke bot`;

export const menuKeyboard = Markup.inlineKeyboard([
    [
        Markup.button.callback('🏛️ Layanan Desa', 'action_layanan'),
    ],
    [
        Markup.button.callback('🏓 Ping', 'action_ping'),
        Markup.button.callback('ℹ️ Info', 'action_info')
    ]
]);

export const menuCommand = async (ctx: Context) => {
    try {
        await ctx.reply(menuMessage, { parse_mode: 'HTML', ...menuKeyboard });
    } catch (error) {
        logger.error('menuCommand', 'Failed to show menu', error);
        await ctx.reply('⚠️ Gagal menampilkan menu.').catch(() => { });
    }
};
