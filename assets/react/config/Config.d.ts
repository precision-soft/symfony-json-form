/*
 * Copyright (c) Precision Soft
 */

export type ConfigType = {
    getAllLocales: () => string[]
};

declare const Config: ConfigType;

export default Config;
