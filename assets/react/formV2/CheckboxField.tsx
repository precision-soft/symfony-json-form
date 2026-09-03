import {Box} from '@mui/material';
import React from 'react';
import type {NameType} from '../component/Icon';
import {default as Icon} from '../component/Icon';

type IconProps = {
    name: NameType
    className?: string
}

export const CheckboxIcon: React.FunctionComponent<IconProps> = (props) => {
    const className = ['checkbox-icon d-flex align-items-center justify-content-center'];
    if (undefined !== props.className) {
        className.push(props.className);
    }

    return (
        <Box className={className.join(' ')}>
            <Icon name={props.name}/>
        </Box>
    );
};

