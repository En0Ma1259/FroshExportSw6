import template from './frosh-export-list.html.twig';

const {Component, Mixin} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('frosh-export-list', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('listing'),
        Mixin.getByName('placeholder')
    ],

    data() {
        return {
            sortBy: 'name',
            defaultSortBy: 'name',
            sortDirection: 'DESC',
            naturalSorting: true,
            entities: null,
            total: 0,
            isLoading: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        froshExportRepository() {
            return this.repositoryFactory.create('frosh_export');
        },

        columns() {
            return [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$tc('frosh-export.detail.name'),
                    routerLink: 'frosh.export.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true
                },
                {
                    property: 'entity',
                    dataIndex: 'entity',
                    label: this.$tc('frosh-export.detail.firstName'),
                    routerLink: 'frosh.export.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true
                },
                {
                    property: 'latestExecute',
                    dataIndex: 'latestExecute',
                    label: this.$tc('frosh-export.detail.latestExecute'),
                    allowResize: true,
                    primary: true
                }
            ];
        },

        defaultCriteria() {
            const criteria = new Criteria(this.page, this.limit);
            criteria.setTerm(this.term);
            return criteria;
        },
    },

    methods: {
        updateTotal({total}) {
            this.total = total;
        },

        getList() {
            this.isLoading = true;

            return this.froshExportRepository.search(this.defaultCriteria, Shopware.Context.api).then((items) => {
                this.total = items.total;
                this.entities = items;
                this.isLoading = false;

                return items;
            }).catch(() => {
                this.isLoading = false;
            });
        }
    }
});
