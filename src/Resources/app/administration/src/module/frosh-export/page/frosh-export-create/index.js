const {Component} = Shopware;

Component.extend('frosh-export', 'frosh-export-detail', {
    methods: {
        getEntity() {
            this.entity = this.froshExportRepository.create(Shopware.Context.api);
            this.entity.default = true;
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
