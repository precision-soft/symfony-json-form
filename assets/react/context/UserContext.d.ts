/*
 * Copyright (c) Precision Soft
 */

import React from 'react';

export type UserContextType = {
    user: {
        accessToken: string | null
    }
};

declare const UserContext: React.Context<UserContextType>;

export default UserContext;
