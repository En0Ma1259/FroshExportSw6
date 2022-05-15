const ApiService = Shopware.Classes.ApiService;

export default class FroshExportService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'sw') {
        super(httpClient, loginService, apiEndpoint);
        this.name = "froshExportService";
    }

    triggerExport(id, additionalParams = {}, additionalHeaders = {}) {
        const params = additionalParams;
        const headers = this.getBasicHeaders(additionalHeaders);

        return this.httpClient
            .get(`/frosh/export/${id}/trigger`, {params, headers})
            .then((response) => {
                return ApiService.handleResponse(response);
            });

    }
}
