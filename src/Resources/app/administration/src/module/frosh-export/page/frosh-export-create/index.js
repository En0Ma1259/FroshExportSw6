import template from './frosh-export-create.html.twig';

const { Component } = Shopware;

Component.extend('frosh-export-create', 'frosh-export-detail', {
    template,

    methods: {
        getEntity() {
            this.entity = this.froshExportRepository.create(Shopware.Context.api);
        },

        onClickSave() {
            this.isLoading = true;

            this.froshExportRepository
                .save(this.entity, Shopware.Context.api)
                .then(() => {
                    this.isLoading = false;
                    this.$router.push({name: 'frosh.export.detail', params: {id: this.entity.id}});
                }).catch((exception) => {
                this.isLoading = false;

                this.createNotificationError({
                    title: this.$tc('frosh-export.detail.errorTitle'),
                    message: exception
                });
            });
        }
    }
});
