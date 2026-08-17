/*
 * Copyright (c) Precision Soft
 */

/** nullable, with `addError` optional: `HttpClient.error()` guards for both before using it. */

import React from 'react';

export type AlertContextType = {
    addError?: (message: string) => void
} | null;

declare const AlertContext: React.Context<AlertContextType>;

export default AlertContext;
