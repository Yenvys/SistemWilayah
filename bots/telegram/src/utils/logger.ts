type LogLevel = 'debug' | 'info' | 'warn' | 'error';

const LEVEL_CONFIG: Record<LogLevel, { priority: number; label: string }> = {
    debug: { priority: 0, label: 'DEBUG' },
    info:  { priority: 1, label: 'INFO ' },
    warn:  { priority: 2, label: 'WARN ' },
    error: { priority: 3, label: 'ERROR' },
};

const MIN_LEVEL: LogLevel = (process.env.LOG_LEVEL as LogLevel) || 'info';

const formatTimestamp = (): string => {
    return new Date().toISOString().replace('T', ' ').slice(0, 19);
};

const log = (level: LogLevel, context: string, message: string, data?: unknown): void => {
    if (LEVEL_CONFIG[level].priority < LEVEL_CONFIG[MIN_LEVEL].priority) return;

    const timestamp = formatTimestamp();
    const label = LEVEL_CONFIG[level].label;
    const prefix = `[${timestamp}] [${label}] [${context}]`;

    if (data !== undefined) {
        if (level === 'error') {
            console.error(prefix, message, data);
        } else {
            console.log(prefix, message, data);
        }
    } else {
        if (level === 'error') {
            console.error(prefix, message);
        } else {
            console.log(prefix, message);
        }
    }
};

export const logger = {
    debug: (context: string, message: string, data?: unknown) => log('debug', context, message, data),
    info:  (context: string, message: string, data?: unknown) => log('info',  context, message, data),
    warn:  (context: string, message: string, data?: unknown) => log('warn',  context, message, data),
    error: (context: string, message: string, data?: unknown) => log('error', context, message, data),
};
