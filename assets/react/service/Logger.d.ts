/*
 * Copyright (c) Precision Soft
 */

export type LoggerType = {
    info: (...messages: any[]) => void
    error: (...messages: any[]) => void
};

declare const logger: LoggerType;

export default logger;
