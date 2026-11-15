import { Context } from 'telegraf';
import type { Message } from 'telegraf/types';
import sharp from 'sharp';
import ffmpeg from 'fluent-ffmpeg';
import ffmpegInstaller from '@ffmpeg-installer/ffmpeg';
import * as fs from 'fs/promises';
import * as path from 'path';
import * as os from 'os';
import { logger } from '../utils/logger.js';

ffmpeg.setFfmpegPath(ffmpegInstaller.path);

const STICKER_SIZE = 512;

export const stickerUsageMessage =
    `🖼️ <b>Sticker Maker</b>\n\n` +
    `Cara menggunakan:\n` +
    `• Reply foto/video/GIF dengan /stc\n` +
    `• Atau kirim foto/video/GIF langsung ke bot\n\n` +
    `📝 Foto → Sticker statis\n` +
    `🎬 Video/GIF → Video sticker (maks 3 detik)\n\n` +
    `<i>Ingin durasi panjang? Gunakan /togif</i>`;

// --- Helper Functions ---

const getTempPath = (ext: string): string =>
    path.join(os.tmpdir(), `cevlt_${Date.now()}_${Math.random().toString(36).slice(2)}.${ext}`);

async function downloadFile(ctx: Context, fileId: string): Promise<Buffer> {
    const url = await ctx.telegram.getFileLink(fileId);
    const res = await fetch(url.href);
    if (!res.ok) throw new Error(`Download failed: ${res.status}`);
    return Buffer.from(await res.arrayBuffer());
}

async function convertImageToSticker(buffer: Buffer): Promise<Buffer> {
    return sharp(buffer)
        .resize(512, 512, { fit: 'inside' })
        .webp({ quality: 100, lossless: true })
        .toBuffer();
}

async function convertVideoToSticker(buffer: Buffer): Promise<Buffer> {
    const inputPath = getTempPath('mp4');
    const outputPath = getTempPath('webm');

    try {
        await fs.writeFile(inputPath, buffer);

        await new Promise<void>((resolve, reject) => {
            ffmpeg(inputPath)
                .inputOptions(['-t', '3'])
                .videoFilter([
                    "scale='if(gt(iw,ih),512,-2)':'if(gt(iw,ih),-2,512)'",
                    'fps=30'
                ])
                .outputOptions([
                    '-c:v', 'libvpx-vp9',
                    '-pix_fmt', 'yuv420p',
                    '-crf', '35',
                    '-b:v', '256k',
                    '-maxrate', '256k',
                    '-bufsize', '512k',
                    '-an',
                    '-t', '3',
                ])
                .output(outputPath)
                .on('end', () => resolve())
                .on('error', (err: Error) => reject(err))
                .run();
        });

        return await fs.readFile(outputPath);
    } finally {
        await fs.unlink(inputPath).catch(() => {});
        await fs.unlink(outputPath).catch(() => {});
    }
}

async function convertVideoToGif(buffer: Buffer): Promise<Buffer> {
    const inputPath = getTempPath('mp4');
    const outputPath = getTempPath('out.mp4');

    try {
        await fs.writeFile(inputPath, buffer);

        await new Promise<void>((resolve, reject) => {
            ffmpeg(inputPath)
                .outputOptions([
                    '-c:v', 'libx264',
                    '-pix_fmt', 'yuv420p',
                    '-preset', 'fast',
                    '-crf', '24',
                    '-an'
                ])
                .output(outputPath)
                .on('end', () => resolve())
                .on('error', (err: Error) => reject(err))
                .run();
        });

        return await fs.readFile(outputPath);
    } finally {
        await fs.unlink(inputPath).catch(() => {});
        await fs.unlink(outputPath).catch(() => {});
    }
}

// --- Media Detection ---

interface MediaInfo {
    type: 'image' | 'video';
    fileId: string;
}

function detectMedia(msg: Message): MediaInfo | null {
    if ('photo' in msg && msg.photo && msg.photo.length > 0) {
        const photo = msg.photo[msg.photo.length - 1];
        if (photo) return { type: 'image', fileId: photo.file_id };
    }
    if ('animation' in msg && msg.animation) {
        return { type: 'video', fileId: msg.animation.file_id };
    }
    if ('video' in msg && msg.video) {
        return { type: 'video', fileId: msg.video.file_id };
    }
    if ('document' in msg && msg.document) {
        const mime = msg.document.mime_type || '';
        if (mime.startsWith('image/')) return { type: 'image', fileId: msg.document.file_id };
        if (mime.startsWith('video/')) return { type: 'video', fileId: msg.document.file_id };
    }
    return null;
}

// --- Core Processing ---

async function processMedia(ctx: Context, media: MediaInfo): Promise<void> {
    if (!ctx.chat) return;

    const statusMsg = await ctx.reply(
        media.type === 'image'
            ? '⏳ Mengkonversi gambar ke sticker...'
            : '⏳ Mengkonversi video ke sticker... (maks 3 detik)'
    );

    try {
        const fileBuffer = await downloadFile(ctx, media.fileId);
        let stickerBuffer: Buffer;

        if (media.type === 'image') {
            stickerBuffer = await convertImageToSticker(fileBuffer);
        } else {
            stickerBuffer = await convertVideoToSticker(fileBuffer);
        }

        await ctx.replyWithSticker({
            source: stickerBuffer,
            filename: media.type === 'image' ? 'sticker.webp' : 'sticker.webm'
        });
        logger.info('sticker', `Converted ${media.type} to sticker successfully`);
    } catch (error) {
        logger.error('sticker', `Failed to convert ${media.type}`, error);
        await ctx.reply('⚠️ Gagal membuat sticker. Pastikan file tidak terlalu besar (maks 20MB).').catch(() => {});
    } finally {
        await ctx.telegram.deleteMessage(ctx.chat.id, statusMsg.message_id).catch(() => {});
    }
}

// --- Exported Handlers ---

export const stickerCommand = async (ctx: Context) => {
    try {
        const msg = ctx.message;
        if (!msg) return;

        if ('reply_to_message' in msg && msg.reply_to_message) {
            const media = detectMedia(msg.reply_to_message);
            if (media) {
                await processMedia(ctx, media);
                return;
            }
        }

        await ctx.reply(stickerUsageMessage, { parse_mode: 'HTML' });
    } catch (error) {
        logger.error('stickerCommand', 'Error processing sticker command', error);
        await ctx.reply('⚠️ Terjadi kesalahan.').catch(() => {});
    }
};

export const handleMediaMessage = async (ctx: Context) => {
    try {
        if (!ctx.message) return;

        const media = detectMedia(ctx.message);
        if (!media) return;

        await processMedia(ctx, media);
    } catch (error) {
        logger.error('handleMediaMessage', 'Error processing media', error);
        await ctx.reply('⚠️ Gagal membuat sticker.').catch(() => {});
    }
};

export const togifUsageMessage =
    `🎬 <b>Video to GIF</b>\n\n` +
    `Cara menggunakan:\n` +
    `• Reply video dengan /togif\n` +
    `• Mengubah video menjadi GIF animasi tanpa batas 3 detik`;

export const togifCommand = async (ctx: Context) => {
    try {
        const msg = ctx.message;
        if (!msg) return;

        if ('reply_to_message' in msg && msg.reply_to_message) {
            const media = detectMedia(msg.reply_to_message);
            if (media && media.type === 'video') {
                if (!ctx.chat) return;

                const statusMsg = await ctx.reply('⏳ Mengkonversi video ke GIF...');

                try {
                    const fileBuffer = await downloadFile(ctx, media.fileId);
                    const gifBuffer = await convertVideoToGif(fileBuffer);
                    
                    await ctx.replyWithAnimation({ source: gifBuffer, filename: 'animation.mp4' });
                    logger.info('togif', `Converted video to GIF successfully`);
                } catch (error) {
                    logger.error('togif', `Failed to convert video to GIF`, error);
                    await ctx.reply('⚠️ Gagal membuat GIF. Pastikan file tidak terlalu besar (maks 20MB).').catch(() => {});
                } finally {
                    await ctx.telegram.deleteMessage(ctx.chat.id, statusMsg.message_id).catch(() => {});
                }
                return;
            } else if (media && media.type === 'image') {
                await ctx.reply('⚠️ /togif hanya untuk video. Untuk foto, gunakan /s').catch(() => {});
                return;
            }
        }

        await ctx.reply(togifUsageMessage, { parse_mode: 'HTML' });
    } catch (error) {
        logger.error('togifCommand', 'Error processing togif command', error);
        await ctx.reply('⚠️ Terjadi kesalahan.').catch(() => {});
    }
};
