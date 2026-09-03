import $ from 'jquery';
import React from 'react';
import AlertContext from '../context/AlertContext';
import type {AlertContextType} from '../context/AlertContext';
import UserContext from '../context/UserContext';
import type {FormDataType} from '../form/Form';
import type {NullableStringArrayType} from '../type/Array';
import type {NullableNullaryType, NullaryType} from '../type/Function';
import type {MapType, NullableMapType} from '../type/Map';
import type {NullableStringType} from '../type/Scalar';
import logger from './Logger';

type ResponseType = MapType & {
    success: boolean,
    data: NullableMapType,
    errors: NullableStringArrayType
}

export type OnSuccessType<T> = (response: T) => void;
/** the response is null when the request failed before a json body was received. */
export type OnCompleteType<T> = (response: T | null) => void;

export enum HttpRequestTypeEnum {
    GET = 'get',
    POST = 'post'
}

type HttpClientRequestType = JQuery.jqXHR;
type AjaxSettingsType = JQuery.AjaxSettings;

export class HttpRequest<RT = ResponseType> {
    private readonly url: string;
    private readonly onSuccess: OnSuccessType<RT>;
    private readonly type: HttpRequestTypeEnum;

    private data: MapType = {};

    private beforeSend: NullableNullaryType = null;
    private onComplete: OnCompleteType<MapType> | null = null;
    private onError: NullableNullaryType = null;

    private httpClientRequest: HttpClientRequestType | null = null;

    constructor(url: string, onSuccess: OnSuccessType<RT>, type: HttpRequestTypeEnum) {
        this.url = url;
        this.onSuccess = onSuccess;
        this.type = type;
    }

    getUrl = (): string => {
        return this.url;
    };

    getOnSuccess = (): OnSuccessType<RT> => {
        return this.onSuccess;
    };

    getType = (): HttpRequestTypeEnum => {
        return this.type;
    };

    getData = (): MapType => {
        return this.data;
    };

    setData = (data: MapType): HttpRequest<RT> => {
        this.data = data;

        return this;
    };

    getBeforeSend = (): NullableNullaryType => {
        return this.beforeSend;
    };

    setBeforeSend = (beforeSend: NullaryType): HttpRequest<RT> => {
        this.beforeSend = beforeSend;

        return this;
    };

    getOnComplete = (): OnCompleteType<MapType> | null => {
        return this.onComplete;
    };

    setOnComplete = (complete: OnCompleteType<MapType>): HttpRequest<RT> => {
        this.onComplete = complete;

        return this;
    };

    getOnError = (): NullableNullaryType => {
        return this.onError;
    };

    setOnError = (error: NullaryType): HttpRequest<RT> => {
        this.onError = error;

        return this;
    };

    setHttpClientRequest = (httpClientRequest: HttpClientRequestType): HttpRequest<RT> => {
        this.httpClientRequest = httpClientRequest;

        return this;
    };

    abort = (message?: string): boolean => {
        if (this.httpClientRequest === null) {
            return false;
        }

        message !== undefined && logger.info(`abort with message "${message}"`);

        this.httpClientRequest.abort();

        this.httpClientRequest = null;

        return true;
    };
}

declare global {
    interface Navigator {
        msSaveBlob?: (blob: unknown, defaultName?: string) => boolean;
    }
}

class HttpClient {
    private accessToken: NullableStringType;
    private alertContext: AlertContextType;

    constructor(
        accessToken: NullableStringType,
        alertContext: AlertContextType
    ) {
        this.accessToken = accessToken;
        this.alertContext = alertContext;
    }

    getFormDataFromResponse = (response: ResponseType): FormDataType | null => {
        const data = this.getDataFromResponse<{ form?: FormDataType }>(response);

        return data?.form ?? null;
    };

    getDataFromResponse = <T>(response: ResponseType): T | null => {
        if (false === response.success) {
            this.error(response.errors);

            return null;
        }

        return <T>response.data;
    };

    download = (httpRequest: HttpRequest): void => {
        httpRequest.abort();

        const ajax = $.ajax(
            {
                ...this.buildAjaxRequest(httpRequest),
                xhrFields: {
                    responseType: 'blob'
                },
                success: (blob, status, xhr) => {
                    let filename = '';
                    const disposition = xhr.getResponseHeader('Content-Disposition');

                    if (null !== disposition && -1 !== disposition.indexOf('attachment')) {
                        const filenameRegex = /filename[^;=\n]*=((["']).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);

                        if (null !== matches && null !== matches[1]) {
                            filename = matches[1].replace(/["']/g, '');
                        }
                    }

                    if (window.navigator.msSaveBlob !== undefined) {
                        window.navigator.msSaveBlob(blob, filename);
                    } else {
                        const url = window.URL || window.webkitURL;
                        const downloadUrl = url.createObjectURL(blob);

                        if (0 < filename.length) {
                            const a = document.createElement('a');

                            if (a.download === undefined) {
                                window.location.href = downloadUrl;
                            } else {
                                a.href = downloadUrl;
                                a.download = filename;
                                document.body.appendChild(a);
                                a.click();
                            }
                        } else {
                            window.location.href = downloadUrl;
                        }

                        setTimeout(
                            () => {
                                url.revokeObjectURL(downloadUrl);
                            },
                            100
                        );
                    }

                    null !== httpRequest.getOnSuccess() && httpRequest.getOnSuccess()(blob);
                }
            }
        );

        httpRequest.setHttpClientRequest(ajax);
    };

    send = (httpRequest: HttpRequest<any>): void => {
        httpRequest.abort();

        const ajax = $.ajax(
            {
                ...this.buildAjaxRequest(httpRequest),
                dataType: 'json',
                contentType: 'application/json'
            }
        );

        httpRequest.setHttpClientRequest(ajax);
    };

    private buildAjaxRequest = (httpRequest: HttpRequest): AjaxSettingsType => {
        const data = httpRequest.getType().toLowerCase() === HttpRequestTypeEnum.GET ? httpRequest.getData() : JSON.stringify(httpRequest.getData());

        logger.info(httpRequest.getType(), httpRequest.getUrl(), data);

        const headers: MapType<string> = {};
        if (null !== this.accessToken && 0 < this.accessToken.length) {
            headers['X-AUTH-TOKEN'] = this.accessToken;
        }

        return {
            url: httpRequest.getUrl(),
            data: data,
            type: httpRequest.getType(),
            xhrFields: {
                withCredentials: true
            },
            headers: headers,
            crossDomain: true,
            beforeSend: () => {
                const beforeSend = httpRequest.getBeforeSend();

                null !== beforeSend && beforeSend();
            },
            success: (response) => {
                null !== httpRequest.getOnSuccess() && httpRequest.getOnSuccess()(response);
            },
            error: (jqXhr, textStatus, errorThrown) => {
                if (textStatus === 'abort') {
                    logger.info('http request aborted');
                    return;
                }

                logger.error(
                    {
                        jqXhr: jqXhr,
                        textStatus: textStatus,
                        errorThrown: errorThrown
                    }
                );

                /** `responseJSON` is absent - not null - when the body is not json, which is exactly when the fallback is wanted. */
                const errors = jqXhr.responseJSON?.errors ?? 'invalid backend response received';

                this.error(errors);

                const onError = httpRequest.getOnError();

                null !== onError && onError();
            },
            complete: (jqXhr) => {
                const onComplete = httpRequest.getOnComplete();

                null !== onComplete && onComplete(
                    this.getXhrJsonResponse(jqXhr)
                );
            }
        };
    };

    /** jquery only sets `responseJSON` when the body actually parsed as json, so an error page yields null. */
    private getXhrJsonResponse = (jqXhr: HttpClientRequestType): ResponseType | null => {
        return (jqXhr?.responseJSON as ResponseType) ?? null;
    };

    private error = (errors: string | string[] | null): void => {
        if (errors === null) {
            return;
        }

        logger.error(errors);

        const alertContext = this.alertContext;

        if (alertContext === null || alertContext.addError === undefined) {
            return;
        }

        const addError = alertContext.addError;

        if (Array.isArray(errors)) {
            errors.map((error: string) => addError(error));

            return;
        }

        addError(errors as string);
    };
}

export const useHttpClient = (): HttpClient => {
    const userContext = React.useContext(UserContext);
    const alertContext = React.useContext(AlertContext);

    return new HttpClient(
        userContext.user.accessToken,
        alertContext
    );
};
