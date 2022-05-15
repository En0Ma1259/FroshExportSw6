import './module/frosh-export';
import './module/frosh-export/page/frosh-export-detail';
import './module/frosh-export/page/frosh-export-list';
import './module/frosh-export/page/frosh-export-create';

import FroshExportService from './service/api/frosh.export.service';

Shopware.Application.addServiceProvider('froshExportService', () => {
    const initContainer = Shopware.Application.getContainer('init');

    return new FroshExportService(initContainer.httpClient, Shopware.Service('loginService'));
});
