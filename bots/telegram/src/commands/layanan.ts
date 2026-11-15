import { Context, Markup } from 'telegraf';
import axios from 'axios';
import { logger } from '../utils/logger.js';

export interface DocumentServiceItem {
    short: string;
    code: string;
    name: string;
    icon: string;
}

export const DOCUMENT_SERVICES: DocumentServiceItem[] = [
    { short: 'SKU', code: 'SURAT_KETERANGAN_USAHA', name: 'Surat Keterangan Usaha (SKU)', icon: '🏢' },
    { short: 'SKTM', code: 'SURAT_KETERANGAN_TIDAK_MAMPU', name: 'Surat Keterangan Tidak Mampu (SKTM)', icon: '🤝' },
    { short: 'NIKAH', code: 'SURAT_PENGANTAR_NIKAH', name: 'Surat Pengantar Nikah (NA)', icon: '💍' },
    { short: 'DOMISILI', code: 'SURAT_KETERANGAN_DOMISILI', name: 'Surat Keterangan Domisili', icon: '🏠' },
    { short: 'WARIS', code: 'SURAT_KETERANGAN_WARIS', name: 'Surat Keterangan Waris', icon: '📜' },
    { short: 'KERAMAIAN', code: 'SURAT_IZIN_KERAMAIAN', name: 'Surat Izin Keramaian', icon: '🎉' },
    { short: 'HILANG', code: 'SURAT_PENGANTAR_KEHILANGAN', name: 'Surat Pengantar Kehilangan', icon: '🔍' },
    { short: 'MEDIASI', code: 'MEDIASI_SENGKETA_WARGA', name: 'Mediasi Sengketa Warga', icon: '⚖️' },
    { short: 'RENOVASI', code: 'IZIN_RENOVASI_RUMAH', name: 'Izin Renovasi Rumah', icon: '🔨' },
    { short: 'TAMU', code: 'PENDATAAN_TAMU_MENGINAP', name: 'Pendataan Tamu Menginap', icon: '👥' },
    { short: 'KAS', code: 'IURAN_KAS_RT', name: 'Iuran Kas RT', icon: '💰' },
    { short: 'REKOM', code: 'SURAT_REKOMENDASI_INTERNAL', name: 'Surat Rekomendasi RT', icon: '⭐' },
];

export const userSessions = new Map<number, {
    step: 'waiting_pengajuan_notes' | 'waiting_pengaduan_text';
    shortCode?: string;
    typeCode?: string;
    typeName?: string;
}>();

export const getLayananKeyboard = () => {
    return Markup.inlineKeyboard([
        [
            Markup.button.callback('📄 Pengajuan Surat', 'action_layanan_pengajuan_menu'),
            Markup.button.callback('📑 Cek Pengajuan', 'action_layanan_cek_pengajuan')
        ],
        [
            Markup.button.callback('📢 Buat Pengaduan', 'action_layanan_pengaduan_menu'),
            Markup.button.callback('📋 Cek Pengaduan', 'action_layanan_cek_pengaduan')
        ],
        [
            Markup.button.callback('🚪 Logout Akun', 'action_logout_confirm'),
            Markup.button.callback('⬅️ Menu Utama', 'action_show_menu')
        ]
    ]);
};

export const layananCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    try {
        const response = await axios.get(`http://siwil.test/api/telegram/user/${telegramId}`);
        const user = response.data.user;

        if (user) {
            const text = `🏛️ <b>LAYANAN WARGA DESA (SIWIL)</b>\n\n` +
                `Halo, <b>${user.name}</b>!\n` +
                `Peran: <b>${user.role}</b>\n\n` +
                `Silakan pilih menu layanan yang tersedia di bawah ini:`;

            const keyboard = getLayananKeyboard();

            if (ctx.callbackQuery) {
                await ctx.editMessageText(text, { parse_mode: 'HTML', ...keyboard });
            } else {
                await ctx.reply(text, { parse_mode: 'HTML', ...keyboard });
            }
        }
    } catch (error: any) {
        if (error.response && error.response.status === 404) {
            const notConnectedText = `❌ <b>Akun Belum Terhubung</b>\n\n` +
                `Akun Telegram Anda belum terhubung dengan sistem desa (SIWIL).\n\n` +
                `Gunakan perintah login berikut untuk menghubungkan akun:\n` +
                `<code>/login [NIK/Email/No.HP] [Password]</code>\n\n` +
                `<i>Contoh: /login 3201234567890001 password123</i>`;

            const notConnectedKeyboard = Markup.inlineKeyboard([
                [Markup.button.callback('📋 Menu Utama', 'action_show_menu')]
            ]);

            if (ctx.callbackQuery) {
                await ctx.editMessageText(notConnectedText, { parse_mode: 'HTML', ...notConnectedKeyboard });
            } else {
                await ctx.reply(notConnectedText, { parse_mode: 'HTML', ...notConnectedKeyboard });
            }
        } else {
            logger.error('layananCommand', 'Error fetching user', error.message);
            await ctx.reply('⚠️ Terjadi kesalahan saat mengambil data layanan.');
        }
    }
};

export const pengajuanMenuCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    // Check if user is linked
    try {
        await axios.get(`http://siwil.test/api/telegram/user/${telegramId}`);
    } catch (e: any) {
        return ctx.reply('❌ Anda belum terhubung. Silakan /login terlebih dahulu.');
    }

    const buttons = [];
    for (let i = 0; i < DOCUMENT_SERVICES.length; i += 2) {
        const row = [];
        const item1 = DOCUMENT_SERVICES[i];
        if (item1) {
            row.push(Markup.button.callback(`${item1.icon} ${item1.short}`, `action_pilih_surat_${item1.short}`));
        }
        const item2 = DOCUMENT_SERVICES[i + 1];
        if (item2) {
            row.push(Markup.button.callback(`${item2.icon} ${item2.short}`, `action_pilih_surat_${item2.short}`));
        }
        if (row.length > 0) {
            buttons.push(row);
        }
    }
    buttons.push([Markup.button.callback('⬅️ Kembali ke Layanan', 'action_layanan')]);

    let text = `📄 <b>MENU PENGAJUAN SURAT KETERANGAN</b>\n\n` +
        `Pilih jenis surat yang ingin Anda ajukan dengan menekan salah satu tombol di bawah:\n\n`;

    DOCUMENT_SERVICES.forEach((item, index) => {
        text += `${item.icon} <b>${item.short}</b>: <i>${item.name}</i>\n`;
    });

    text += `\n<i>Atau Anda dapat langsung mengetik:</i>\n<code>/pengajuan [Kode] [Keperluan]</code>`;

    const keyboard = Markup.inlineKeyboard(buttons);

    if (ctx.callbackQuery) {
        await ctx.editMessageText(text, { parse_mode: 'HTML', ...keyboard });
    } else {
        await ctx.reply(text, { parse_mode: 'HTML', ...keyboard });
    }
};

export const pilihSuratHandler = async (ctx: Context, shortCode: string) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    const item = DOCUMENT_SERVICES.find(s => s.short === shortCode.toUpperCase());
    if (!item) {
        return ctx.reply('⚠️ Jenis surat tidak ditemukan.');
    }

    userSessions.set(telegramId, {
        step: 'waiting_pengajuan_notes',
        shortCode: item.short,
        typeCode: item.code,
        typeName: item.name
    });

    const text = `📝 <b>Pengajuan Surat: ${item.name}</b>\n\n` +
        `Silakan balas pesan ini dengan mengetik <b>Keperluan / Keterangan</b> surat Anda.\n\n` +
        `<i>Contoh:</i>\n<code>Untuk kelengkapan administrasi beasiswa sekolah</code>\n\n` +
        `<i>Ketik <code>/batal</code> jika ingin membatalkan.</i>`;

    const keyboard = Markup.inlineKeyboard([
        [Markup.button.callback('❌ Batal Pengajuan', 'action_batal_pengajuan')]
    ]);

    if (ctx.callbackQuery) {
        await ctx.editMessageText(text, { parse_mode: 'HTML', ...keyboard });
    } else {
        await ctx.reply(text, { parse_mode: 'HTML', ...keyboard });
    }
};

export const batalPengajuanHandler = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (telegramId) {
        userSessions.delete(telegramId);
    }
    await ctx.reply('Pengajuan dibatalkan.');
    return layananCommand(ctx);
};

export const pengajuanCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    // @ts-ignore
    const text = ctx.message?.text || '';
    const args = text.split(' ').slice(1);

    if (args.length < 2) {
        return pengajuanMenuCommand(ctx);
    }

    const shortCode = args[0].toUpperCase();
    const notes = args.slice(1).join(' ');

    const item = DOCUMENT_SERVICES.find(s => s.short === shortCode);
    if (!item) {
        return ctx.reply(
            '⚠️ Kode surat tidak dikenali.\n' +
            'Ketik /layanan atau gunakan tombol untuk memilih dari daftar surat yang tersedia.'
        );
    }

    try {
        const loadingMsg = await ctx.reply('Mengirim pengajuan surat Anda...');

        const response = await axios.post('http://siwil.test/api/telegram/documents', {
            telegram_id: telegramId.toString(),
            type: item.code,
            notes: notes
        });

        const reqNumber = response.data.data?.request_number || 'N/A';

        const keyboard = Markup.inlineKeyboard([
            [Markup.button.callback('📑 Cek Status Pengajuan', 'action_layanan_cek_pengajuan')],
            [Markup.button.callback('🏛️ Kembali ke Layanan', 'action_layanan')]
        ]);

        await ctx.telegram.editMessageText(
            telegramId,
            loadingMsg.message_id,
            undefined,
            `✅ <b>Pengajuan Surat Berhasil Dibuat!</b>\n\n` +
            `• <b>Nomor Surat:</b> <code>${reqNumber}</code>\n` +
            `• <b>Jenis:</b> ${item.name}\n` +
            `• <b>Keperluan:</b> <i>${notes}</i>\n` +
            `• <b>Status Awal:</b> 📝 <b>Menunggu Verifikasi RT</b>\n\n` +
            `Surat Anda sedang dalam proses verifikasi oleh pengurus RT. Anda akan menerima notifikasi Telegram dan file PDF secara otomatis saat surat disetujui!`,
            { parse_mode: 'HTML', ...keyboard }
        );
    } catch (error: any) {
        const errorMsg = error.response?.data?.error || 'Terjadi kesalahan saat membuat pengajuan.';
        await ctx.reply(`❌ Gagal: ${errorMsg}`);
    }
};

export const cekPengajuanCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    try {
        const response = await axios.get(`http://siwil.test/api/telegram/documents/${telegramId}`);
        const docs = response.data.data;

        if (!docs || docs.length === 0) {
            const emptyKeyboard = Markup.inlineKeyboard([
                [Markup.button.callback('📄 Ajukan Surat Baru', 'action_layanan_pengajuan_menu')],
                [Markup.button.callback('⬅️ Kembali ke Layanan', 'action_layanan')]
            ]);

            const emptyText = 'Belum ada data pengajuan surat di akun Anda.';
            if (ctx.callbackQuery) {
                return ctx.editMessageText(emptyText, { ...emptyKeyboard });
            }
            return ctx.reply(emptyText, { ...emptyKeyboard });
        }

        const statusMap: Record<string, { label: string; icon: string }> = {
            'DIAJUKAN_RT': { label: 'Menunggu Verifikasi RT', icon: '📝' },
            'DIAJUKAN_RW': { label: 'Disetujui RT (Menunggu Verifikasi RW)', icon: '⏳' },
            'DIAJUKAN_DESA': { label: 'Disetujui RW (Menunggu Verifikasi Desa)', icon: '⏳' },
            'DISETUJUI': { label: 'Disetujui & Siap Dicetak', icon: '✅' },
            'SELESAI': { label: 'Selesai & Diterbitkan', icon: '✅' },
            'DITOLAK': { label: 'Ditolak', icon: '❌' },
        };

        let reply = '📄 <b>Riwayat Pengajuan Surat Anda (5 Terakhir):</b>\n\n';
        const buttons: any[] = [];

        docs.forEach((d: any, i: number) => {
            const st = statusMap[d.status] || { label: d.status, icon: 'ℹ️' };
            const typeLabel = d.type_name || d.type.replace(/_/g, ' ');
            const isApproved = d.status === 'DISETUJUI' || d.status === 'SELESAI' || d.can_download;

            reply += `<b>${i + 1}. ${d.request_number}</b>\n`;
            reply += `   📑 Jenis: <i>${typeLabel}</i>\n`;
            reply += `   ${st.icon} Status: <b>${st.label}</b>\n`;
            if (d.notes) {
                reply += `   📝 Keperluan: <i>"${d.notes}"</i>\n`;
            }
            if (isApproved) {
                reply += `   📥 <b>File PDF Tersedia untuk diunduh!</b>\n`;
                // Add download button for this document
                buttons.push([
                    Markup.button.callback(`📥 Unduh PDF (${d.request_number})`, `action_dl_${d.request_number}`)
                ]);
            }
            reply += '\n';
        });

        buttons.push([
            Markup.button.callback('🔄 Segarkan', 'action_layanan_cek_pengajuan'),
            Markup.button.callback('📄 Ajukan Surat', 'action_layanan_pengajuan_menu')
        ]);
        buttons.push([
            Markup.button.callback('⬅️ Kembali ke Layanan', 'action_layanan')
        ]);

        const keyboard = Markup.inlineKeyboard(buttons);

        if (ctx.callbackQuery) {
            await ctx.editMessageText(reply, { parse_mode: 'HTML', ...keyboard });
        } else {
            await ctx.reply(reply, { parse_mode: 'HTML', ...keyboard });
        }
    } catch (error: any) {
        logger.error('cekPengajuanCommand', 'Error fetching documents', error.response?.data || error.message);
        const errMsg = error.response?.data?.error || 'Gagal mengambil data pengajuan surat.';
        await ctx.reply(`❌ ${errMsg}`);
    }
};

export const downloadSuratHandler = async (ctx: Context, requestNumber: string) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    let loadingMsg: any = null;
    try {
        loadingMsg = await ctx.reply(`⏳ <b>Mengunduh dokumen surat ${requestNumber}...</b>\nMohon tunggu beberapa detik file PDF sedang dipersiapkan.`, {
            parse_mode: 'HTML'
        });

        const response = await axios.get(`http://siwil.test/api/telegram/documents/${requestNumber}/pdf?base64=1`);

        if (!response.data || !response.data.base64) {
            throw new Error('File PDF tidak dapat dibuat atau belum tersedia.');
        }

        const pdfBuffer = Buffer.from(response.data.base64, 'base64');
        const filename = response.data.filename || `Surat_${requestNumber}.pdf`;
        const docInfo = response.data.doc;

        await ctx.replyWithDocument(
            { source: pdfBuffer, filename: filename },
            {
                caption: `📄 <b>Dokumen Resmi SIWIL</b>\n\n` +
                    `• <b>Nomor Surat:</b> <code>${requestNumber}</code>\n` +
                    `• <b>Jenis:</b> ${docInfo?.type || 'Surat Keterangan'}\n` +
                    `• <b>Status:</b> ✅ ${docInfo?.status || 'Disetujui'}\n\n` +
                    `<i>Dokumen resmi SIWIL siap langsung dicetak pada kertas A4. Dokumen ini dilengkapi barcode / QR Code verifikasi keabsahan.</i>`,
                parse_mode: 'HTML'
            }
        );

        if (loadingMsg) {
            await ctx.telegram.deleteMessage(ctx.chat.id, loadingMsg.message_id).catch(() => {});
        }
    } catch (error: any) {
        logger.error('downloadSuratHandler', 'Error downloading PDF', error.response?.data || error.message);
        const errorText = error.response?.data?.error || 'Gagal mengunduh dokumen. Surat mungkin masih dalam tahap verifikasi.';
        if (loadingMsg) {
            await ctx.telegram.editMessageText(
                ctx.chat.id,
                loadingMsg.message_id,
                undefined,
                `❌ <b>Download Gagal:</b> ${errorText}`,
                { parse_mode: 'HTML' }
            ).catch(() => {});
        } else {
            await ctx.reply(`❌ <b>Download Gagal:</b> ${errorText}`, { parse_mode: 'HTML' });
        }
    }
};

export const pengaduanMenuCommand = async (ctx: Context) => {
    const text = `📢 <b>BUAT PENGADUAN WARGA</b>\n\n` +
        `Anda dapat menyampaikan aspirasi, keluhan, atau laporan fasilitas umum melalui perintah:\n\n` +
        `<code>/pengaduan [Judul] [Keterangan Lengkap]</code>\n\n` +
        `<i>Contoh:</i>\n` +
        `<code>/pengaduan Lampu Jalan Mati Lampu penerangan di gang RT 02 mati sejak 2 hari lalu.</code>\n\n` +
        `Pengaduan Anda akan langsung diteruskan ke pengurus RT dan admin wilayah.`;

    const keyboard = Markup.inlineKeyboard([
        [Markup.button.callback('📋 Cek Riwayat Pengaduan', 'action_layanan_cek_pengaduan')],
        [Markup.button.callback('⬅️ Kembali ke Layanan', 'action_layanan')]
    ]);

    if (ctx.callbackQuery) {
        await ctx.editMessageText(text, { parse_mode: 'HTML', ...keyboard });
    } else {
        await ctx.reply(text, { parse_mode: 'HTML', ...keyboard });
    }
};

export const pengaduanCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    // @ts-ignore
    const text = ctx.message?.text || '';
    const args = text.split(' ').slice(1);

    if (args.length < 2) {
        return pengaduanMenuCommand(ctx);
    }

    let title = args[0];
    let description = args.slice(1).join(' ');

    const match = text.match(/\/pengaduan\s+"([^"]+)"\s+(.+)/);
    if (match) {
        title = match[1];
        description = match[2];
    }

    try {
        const response = await axios.post('http://siwil.test/api/telegram/complaints', {
            telegram_id: telegramId.toString(),
            title: title,
            description: description
        });

        const keyboard = Markup.inlineKeyboard([
            [Markup.button.callback('📋 Cek Pengaduan', 'action_layanan_cek_pengaduan')],
            [Markup.button.callback('🏛️ Kembali ke Layanan', 'action_layanan')]
        ]);

        await ctx.reply(
            `✅ <b>${response.data.message}</b>\n\n` +
            `• <b>Judul:</b> ${title}\n` +
            `• <b>Laporan:</b> <i>"${description}"</i>\n\n` +
            `Pengaduan Anda telah tercatat dan sedang menunggu tindak lanjut pengurus.`,
            { parse_mode: 'HTML', ...keyboard }
        );
    } catch (error: any) {
        const errorMsg = error.response?.data?.error || 'Terjadi kesalahan saat membuat pengaduan.';
        await ctx.reply(`❌ Gagal: ${errorMsg}`);
    }
};

export const cekPengaduanCommand = async (ctx: Context) => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return;

    try {
        const response = await axios.get(`http://siwil.test/api/telegram/complaints/${telegramId}`);
        const complaints = response.data.data;

        if (!complaints || complaints.length === 0) {
            const emptyKeyboard = Markup.inlineKeyboard([
                [Markup.button.callback('📢 Buat Pengaduan', 'action_layanan_pengaduan_menu')],
                [Markup.button.callback('⬅️ Kembali ke Layanan', 'action_layanan')]
            ]);

            const emptyText = 'Belum ada data pengaduan yang Anda ajukan.';
            if (ctx.callbackQuery) {
                return ctx.editMessageText(emptyText, { ...emptyKeyboard });
            }
            return ctx.reply(emptyText, { ...emptyKeyboard });
        }

        const statusMap: Record<string, { label: string; icon: string }> = {
            'PENDING': { label: 'Menunggu Tindak Lanjut', icon: '⏳' },
            'PROCESSING': { label: 'Sedang Diproses', icon: '🔄' },
            'RESOLVED': { label: 'Selesai Ditangani', icon: '✅' },
            'REJECTED': { label: 'Ditolak / Belum Dapat Ditindaklanjuti', icon: '❌' },
        };

        let reply = '📢 <b>Riwayat Pengaduan Anda (5 Terakhir):</b>\n\n';
        complaints.forEach((c: any, i: number) => {
            const st = statusMap[c.status] || { label: c.status, icon: 'ℹ️' };
            reply += `<b>${i + 1}. ${c.title}</b>\n`;
            if (c.description) {
                const descSnippet = c.description.length > 70 ? c.description.substring(0, 70) + '...' : c.description;
                reply += `   📝 <i>"${descSnippet}"</i>\n`;
            }
            reply += `   ${st.icon} Status: <b>${st.label}</b>\n\n`;
        });

        const keyboard = Markup.inlineKeyboard([
            [
                Markup.button.callback('🔄 Segarkan', 'action_layanan_cek_pengaduan'),
                Markup.button.callback('📢 Buat Pengaduan', 'action_layanan_pengaduan_menu')
            ],
            [
                Markup.button.callback('⬅️ Kembali ke Layanan', 'action_layanan')
            ]
        ]);

        if (ctx.callbackQuery) {
            await ctx.editMessageText(reply, { parse_mode: 'HTML', ...keyboard });
        } else {
            await ctx.reply(reply, { parse_mode: 'HTML', ...keyboard });
        }
    } catch (error: any) {
        await ctx.reply('❌ Gagal mengambil data pengaduan.');
    }
};

export const handleLayananSessionText = async (ctx: Context): Promise<boolean> => {
    const telegramId = ctx.chat?.id;
    if (!telegramId) return false;

    const session = userSessions.get(telegramId);
    if (!session) return false;

    // @ts-ignore
    const text = ctx.message?.text || '';

    if (text.trim() === '/batal') {
        userSessions.delete(telegramId);
        await ctx.reply('Pengajuan telah dibatalkan.');
        await layananCommand(ctx);
        return true;
    }

    if (session.step === 'waiting_pengajuan_notes') {
        const typeCode = session.typeCode!;
        const typeName = session.typeName!;
        const notes = text.trim();

        userSessions.delete(telegramId);

        try {
            const loadingMsg = await ctx.reply(`Mengirim pengajuan <b>${typeName}</b>...`, { parse_mode: 'HTML' });

            const response = await axios.post('http://siwil.test/api/telegram/documents', {
                telegram_id: telegramId.toString(),
                type: typeCode,
                notes: notes
            });

            const reqNumber = response.data.data?.request_number || 'N/A';

            const keyboard = Markup.inlineKeyboard([
                [Markup.button.callback('📑 Cek Status Pengajuan', 'action_layanan_cek_pengajuan')],
                [Markup.button.callback('🏛️ Kembali ke Layanan', 'action_layanan')]
            ]);

            await ctx.telegram.editMessageText(
                telegramId,
                loadingMsg.message_id,
                undefined,
                `✅ <b>Pengajuan Surat Berhasil Dibuat!</b>\n\n` +
                `• <b>Nomor Surat:</b> <code>${reqNumber}</code>\n` +
                `• <b>Jenis:</b> ${typeName}\n` +
                `• <b>Keperluan:</b> <i>${notes}</i>\n` +
                `• <b>Status Awal:</b> 📝 <b>Menunggu Verifikasi RT</b>\n\n` +
                `Surat Anda sedang diverifikasi oleh RT setempat. Anda akan mendapatkan notifikasi dan file PDF dokumen resmi secara otomatis di bot ini saat surat disetujui!`,
                { parse_mode: 'HTML', ...keyboard }
            );
        } catch (error: any) {
            const errorMsg = error.response?.data?.error || 'Terjadi kesalahan saat membuat pengajuan.';
            await ctx.reply(`❌ Gagal: ${errorMsg}`);
        }
        return true;
    }

    return false;
};
