import { Context } from 'telegraf';
import * as os from 'os';

export const infoCommand = async (ctx: Context) => {
    try {
        const totalMem = (os.totalmem() / 1024 / 1024 / 1024).toFixed(2);
        const freeMem = (os.freemem() / 1024 / 1024 / 1024).toFixed(2);
        const usedMem = (parseFloat(totalMem) - parseFloat(freeMem)).toFixed(2);

        const uptimeHours = Math.floor(os.uptime() / 3600);
        const uptimeMinutes = Math.floor((os.uptime() % 3600) / 60);

        const infoMsg =
            `🤖 <b>Celt Information</b>\n` +
            `Name: celt\n` +
            `Framework: Telegraf (TypeScript)\n\n` +
            `💻 <b>Server / Device Information</b>\n` +
            `OS: ${os.type()} ${os.release()} (${os.arch()})\n` +
            `CPU: ${os.cpus()[0]?.model || 'Unknown CPU'}\n` +
            `RAM: ${usedMem} GB / ${totalMem} GB (Free: ${freeMem} GB)\n` +
            `Uptime: ${uptimeHours}h ${uptimeMinutes}m`;

        await ctx.reply(infoMsg, { parse_mode: 'HTML' });
    } catch (error) {
        console.error('[infoCommand] Error:', error);
        await ctx.reply('⚠️ Gagal mengambil info server.').catch(() => { });
    }
};
