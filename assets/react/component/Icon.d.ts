/*
 * Copyright (c) Precision Soft
 */

import React from 'react';

export type NameType = string;

export type IconPropsType = {
    name: NameType
};

declare const Icon: React.FunctionComponent<IconPropsType>;

export default Icon;
