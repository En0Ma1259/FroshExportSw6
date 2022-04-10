import template from './frosh-export-detail.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('frosh-export-detail', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            entity: null,
            isLoading: false,
            processSuccess: false,
        };
    },

    computed: {
        froshExportRepository() {
            return this.repositoryFactory.create('frosh_export');
        }
    },

    created() {
        this.getEntity();
    },

    methods: {
        getEntity() {
            const criteria = new Criteria();

            this.froshExportRepository
                .get(this.$route.params.id, Shopware.Context.api, criteria)
                .then((entity) => {
                    this.entity = entity;
                });
        },

        onCancel() {
            this.$router.push({name: 'frosh.export.index'});
        },

        onClickSave() {
            this.isLoading = true;

            this.froshExportRepository
                .save(this.entity, Shopware.Context.api)
                .then(() => {
                    this.getEntity();
                    this.isLoading = false;
                    this.processSuccess = true;
                }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('frosh-export.detail.errorTitle'),
                    message: exception
                });
            });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
