/*
 * Copyright (c) Precision Soft
 */

import React from 'react';

export type LanguageContextType = {
    translate: (key: string, parameters?: { [name: string]: any }, domain?: string) => string
    getLocale: () => string
};

export declare const useLanguageContext: () => LanguageContextType;

declare const LanguageContext: React.Context<LanguageContextType>;

export default LanguageContext;
