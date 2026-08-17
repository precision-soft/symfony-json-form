/*
 * Copyright (c) Precision Soft
 */

import React from 'react';

export type UrlGeneratorType = {
    generate: (route: string, parameters?: { [name: string]: any } | null) => string
};

export declare const useUrlGeneratorContext: () => UrlGeneratorType;

declare const UrlGeneratorContext: React.Context<UrlGeneratorType>;

export default UrlGeneratorContext;
