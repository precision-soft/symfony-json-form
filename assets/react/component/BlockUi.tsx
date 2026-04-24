import '../../../css/shared/blockui.scss';
import {Box} from '@mui/material';
import Backdrop from '@mui/material/Backdrop';
import CircularProgress from '@mui/material/CircularProgress';

import React from 'react';
import {StringArrayType} from '../type/Array';

type BlockUiProps = React.PropsWithChildren & {
    open: boolean
    fixed?: boolean
    className?: string
}

export const BlockUi: React.FunctionComponent<BlockUiProps> = (props) => {
    const position: string = (undefined !== props.fixed ? props.fixed : false) ? 'fixed' : 'absolute';

    const classNames: StringArrayType = ['blockui-container'];
    if (undefined !== props.className) {
        classNames.push(props.className);
    }

    return (
        <Box className={classNames.join(' ')}>
            <Backdrop sx={{color: '#FFFFFF', zIndex: (theme) => theme.zIndex.drawer + 1, position: position}}
                      open={props.open}
            >
                <CircularProgress color="inherit"/>
            </Backdrop>

            <Box className="h-100 w-100">{props.children}</Box>
        </Box>
    );
};
