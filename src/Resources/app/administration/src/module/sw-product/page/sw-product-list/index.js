import template from './sw-product-list.html.twig';

Shopware.Component.override('sw-product-list', {
    template,

    inject: ['froshExportService'],

    methods: {
        exportView() {
            const criteria = this.productCriteria;
            criteria.limit = null;
            criteria.offset = null;

            this.froshExportService.createExport('product', criteria);
        }
    }
});
