import React from 'react';
/* the next two resolve in the host application, four levels up; there is nothing above this repository to typecheck them against. */
// @ts-ignore host provided
import router from '../../../../public/bundles/fosjsrouting/js/router';
// @ts-ignore host provided
import routes from '../../../../public/js/fos_js_routes.json';
import LanguageContext from '../context/LanguageContext';
import type {NullableMapType} from '../type/Map';

router.setRoutingData(routes);

class UrlGenerator {
    private readonly locale: string;

    constructor(locale: string) {
        this.locale = locale;
    }

    generate = (route: string, parameters?: NullableMapType): string => {
        parameters = {
            _locale: this.locale,
            ...parameters
        };

        return router.generate(route, parameters, true);
    };
}

const useUrlGenerator = () => {
    const languageContext = React.useContext(LanguageContext);

    const locale = languageContext.getLocale();

    return new UrlGenerator(locale);
};

export default useUrlGenerator;
