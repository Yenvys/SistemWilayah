import axios from 'axios';
import { config } from '../config.js';
import { logger } from '../utils/logger.js';

const client = axios.create({
  baseURL: config.siwilApiUrl,
  timeout: 8000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

export const siwilApi = {
  /**
   * Check if user is linked to SIWIL
   */
  async checkUser(whatsappNumber) {
    try {
      const response = await client.post('/check-user', { whatsapp_number: whatsappNumber });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        linked: false,
        message: error.response?.data?.message || 'Nomor belum terdaftar di sistem SIWIL'
      };
    }
  },

  /**
   * Login & link WhatsApp number to SIWIL account
   */
  async login(login, password, whatsappNumber) {
    try {
      const response = await client.post('/login', {
        login,
        password,
        whatsapp_number: whatsappNumber
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || error.response?.data?.message || 'Gagal login ke SIWIL.'
      };
    }
  },

  /**
   * Unlink account (logout)
   */
  async logout(whatsappNumber) {
    try {
      const response = await client.post('/logout', { whatsapp_number: whatsappNumber });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || 'Gagal logout dari akun SIWIL.'
      };
    }
  },

  /**
   * Get document services list
   */
  async getDocumentServices() {
    try {
      const response = await client.get('/services');
      return { success: true, data: response.data?.data || [] };
    } catch (error) {
      logger.error('Error fetching document services:', error.message);
      return { success: false, data: [] };
    }
  },

  /**
   * Create document request
   */
  async createDocumentRequest(whatsappNumber, type, notes) {
    try {
      const response = await client.post('/documents', {
        whatsapp_number: whatsappNumber,
        type,
        notes: notes || null
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || error.response?.data?.message || 'Gagal membuat pengajuan surat.'
      };
    }
  },

  /**
   * Get citizen document requests history
   */
  async getDocumentRequests(whatsappNumber) {
    try {
      const response = await client.get(`/documents/${whatsappNumber}`);
      return { success: true, data: response.data?.data || [] };
    } catch (error) {
      logger.error('Error fetching document requests:', error.message);
      return { success: false, data: [] };
    }
  },

  /**
   * Download printable document PDF (base64)
   */
  async downloadDocumentPdf(identifier) {
    try {
      const response = await client.get(`/documents/${encodeURIComponent(identifier)}/pdf`, {
        params: { base64: '1' }
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || 'Gagal mengunduh dokumen PDF.'
      };
    }
  },

  /**
   * Create complaint
   */
  async createComplaint(whatsappNumber, title, description, location) {
    try {
      const response = await client.post('/complaints', {
        whatsapp_number: whatsappNumber,
        title,
        description,
        location: location || null
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || error.response?.data?.message || 'Gagal mengirim pengaduan.'
      };
    }
  },

  /**
   * Get citizen complaints history
   */
  async getComplaints(whatsappNumber) {
    try {
      const response = await client.get(`/complaints/${whatsappNumber}`);
      return { success: true, data: response.data?.data || [] };
    } catch (error) {
      logger.error('Error fetching complaints:', error.message);
      return { success: false, data: [] };
    }
  },

  /**
   * Get accessible polls
   */
  async getPolls(whatsappNumber) {
    try {
      const response = await client.get(`/polls/${whatsappNumber}`);
      return { success: true, data: response.data?.data || [] };
    } catch (error) {
      logger.error('Error fetching polls:', error.message);
      return { success: false, data: [] };
    }
  },

  /**
   * Vote on poll
   */
  async votePoll(whatsappNumber, pollId, optionNumber) {
    try {
      const response = await client.post('/polls/vote', {
        whatsapp_number: whatsappNumber,
        poll_id: pollId,
        option_number: parseInt(optionNumber, 10)
      });
      return { success: true, data: response.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.error || 'Gagal memberikan suara pada polling.'
      };
    }
  }
};
