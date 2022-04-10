const ApiService = Shopware.Classes.ApiService;

export default class FroshExportService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'sw') {
        super(httpClient, loginService, apiEndpoint);
        this.name = "froshExportService";
    }

    createExport(entity, criteria) {
        this.httpClient.post(`/frosh/export/listing/${entity}`, criteria.parse());
    }

    triggerExport(id) {
        this.httpClient.get(`/frosh/export/trigger/${id}`);
    }
}
