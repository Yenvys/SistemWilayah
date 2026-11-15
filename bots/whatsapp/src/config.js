import dotenv from 'dotenv';
dotenv.config();

export const config = {
  port: parseInt(process.env.PORT || '3000', 10),
  siwilApiUrl: (process.env.SIWIL_API_URL || 'http://siwil.test/api/whatsapp').replace(/\/+$/, ''),
  authFolder: process.env.AUTH_FOLDER || './auth_info_baileys',
  botName: process.env.BOT_NAME || 'Bot WhatsApp SIWIL'
};
