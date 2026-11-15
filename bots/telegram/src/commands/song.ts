import { Context } from 'telegraf';
import SpotifyWebApi from 'spotify-web-api-node';
import YouTube from 'youtube-sr';
import ytDlp from 'yt-dlp-exec';
import * as path from 'path';
import * as os from 'os';
import * as fs from 'fs/promises';
import { logger } from '../utils/logger.js';

const spotifyApi = new SpotifyWebApi({
    clientId: process.env.SPOTIFY_CLIENT_ID,
    clientSecret: process.env.SPOTIFY_CLIENT_SECRET
});

let spotifyTokenExpireTime = 0;

async function getSpotifyToken() {
    if (Date.now() > spotifyTokenExpireTime) {
        const data = await spotifyApi.clientCredentialsGrant();
        spotifyApi.setAccessToken(data.body['access_token']);
        spotifyTokenExpireTime = Date.now() + (data.body['expires_in'] * 1000) - 60000;
    }
}

const getTempPath = (ext: string): string =>
    path.join(os.tmpdir(), `cevlt_song_${Date.now()}_${Math.random().toString(36).slice(2)}.${ext}`);

export const songUsageMessage =
    `🎵 <b>Song Downloader</b>\n\n` +
    `Ketik perintah:\n` +
    `• <code>/song &lt;judul lagu&gt;</code>\n` +
    `• <code>/song &lt;url youtube&gt;</code>\n\n` +
    `<i>Contoh: /song dewa 19 kangen</i>`;

export const songCommand = async (ctx: Context) => {
    try {
        if (!ctx.message || !('text' in ctx.message)) return;

        const text = ctx.message.text;
        const query = text.split(' ').slice(1).join(' ').trim();

        if (!query) {
            return ctx.reply('🎵 Format: /song <judul lagu> atau /song <url youtube>');
        }

        const statusMsg = await ctx.reply('🔍 Sedang mencari...');

        let youtubeUrl = '';
        let title = '';
        let artist = '';
        let thumbnail = '';

        if (query.includes('youtube.com') || query.includes('youtu.be')) {
            youtubeUrl = query;
            try {
                // @ts-ignore
                const videoInfo = await YouTube.default.getVideo(query);
                if (videoInfo) {
                    if (videoInfo.title && videoInfo.title.includes(' - ')) {
                        const parts = videoInfo.title.split(' - ');
                        artist = parts[0]?.trim() || '';
                        title = parts.slice(1).join(' - ').trim();
                    } else {
                        title = videoInfo.title || '';
                        artist = videoInfo.channel?.name || '';
                        
                        if (artist.endsWith(' - Topic')) artist = artist.replace(' - Topic', '');
                        if (artist.endsWith('VEVO')) artist = artist.replace('VEVO', '');
                    }
                }
            } catch (err) {
                logger.warn('songCommand', 'Failed to fetch video info for URL', err);
            }
        } else {
            // Search Spotify
            try {
                if (process.env.SPOTIFY_CLIENT_ID && process.env.SPOTIFY_CLIENT_SECRET && process.env.SPOTIFY_CLIENT_SECRET !== 'your_spotify_secret_here') {
                    await getSpotifyToken();
                    const searchResult = await spotifyApi.searchTracks(query, { limit: 1 });
                    const track = searchResult.body.tracks?.items[0];

                    if (track) {
                        title = track.name;
                        artist = track.artists.map(a => a.name).join(', ');
                        thumbnail = track.album.images[0]?.url || '';

                        // @ts-ignore
                        const ytSearch = await YouTube.default.searchOne(`${title} ${artist} audio`);
                        if (ytSearch && ytSearch.url) {
                            youtubeUrl = ytSearch.url;
                        }
                    }
                }
            } catch (err) {
                logger.error('songCommand', 'Spotify error, falling back to YouTube', err);
            }

            // Fallback to youtube search if spotify fails or returns empty
            if (!youtubeUrl) {
                // @ts-ignore
                const ytSearch = await YouTube.default.searchOne(query);
                if (ytSearch && ytSearch.url) {
                    youtubeUrl = ytSearch.url;
                    
                    // Parse "Artist - Title" format if it exists
                    if (ytSearch.title && ytSearch.title.includes(' - ')) {
                        const parts = ytSearch.title.split(' - ');
                        artist = parts[0]?.trim() || '';
                        title = parts.slice(1).join(' - ').trim();
                    } else {
                        title = ytSearch.title || 'Unknown Title';
                        artist = ytSearch.channel?.name || '';
                        
                        // Clean up " - Topic" or "VEVO" from channel name
                        if (artist.endsWith(' - Topic')) artist = artist.replace(' - Topic', '');
                        if (artist.endsWith('VEVO')) artist = artist.replace('VEVO', '');
                    }
                    
                    thumbnail = ytSearch.thumbnail?.url || '';
                }
            }
        }

        if (!youtubeUrl) {
            if (ctx.chat?.id) {
                return ctx.telegram.editMessageText(
                    ctx.chat.id,
                    statusMsg.message_id,
                    undefined,
                    '❌ Lagu tidak ditemukan atau kredensial Spotify belum lengkap.'
                );
            }
            return;
        }

        if (ctx.chat?.id) {
            await ctx.telegram.editMessageText(
                ctx.chat.id,
                statusMsg.message_id,
                undefined,
                '⏳ Sedang mendownload audio...'
            );
        }

        const outputPath = getTempPath('mp3');

        await ytDlp(youtubeUrl, {
            extractAudio: true,
            audioFormat: 'mp3',
            output: outputPath,
            noCheckCertificate: true,
            noWarnings: true,
            addMetadata: true
        });

        if (ctx.chat?.id) {
            await ctx.telegram.editMessageText(
                ctx.chat.id,
                statusMsg.message_id,
                undefined,
                '⬆️ Sedang mengirim...'
            );
        }

        const caption = title && artist ? `<b>${title}</b>\n👤 ${artist}` : (title ? `🎵 <b>${title}</b>` : '');

        await ctx.replyWithAudio(
            { source: outputPath, filename: `${title || 'Audio'}.mp3` },
            {
                caption,
                parse_mode: 'HTML',
                title: title || 'Unknown Title',
                performer: artist || 'Unknown Artist'
            }
        );

        if (ctx.chat?.id) {
            await ctx.telegram.deleteMessage(ctx.chat.id, statusMsg.message_id).catch(() => { });
        }
        await fs.unlink(outputPath).catch(() => { });

    } catch (error) {
        logger.error('songCommand', 'Error processing song command', error);
        await ctx.reply('⚠️ Terjadi kesalahan saat mendownload lagu.').catch(() => { });
    }
};
