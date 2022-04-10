import './page/frosh-export-create';
import './page/frosh-export-list';
import './page/frosh-export-detail';

const {Module} = Shopware;

Module.register('frosh-export', {
    type: 'plugin',
    name: 'frosh-export.general.mainName',
    title: 'frosh-export.general.mainMenuItemGeneral',
    description: 'frosh-export.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',
    entity: 'frosh_export',

    routes: {
        index: {
            component: 'frosh-export-list',
            path: 'list',
            meta: {
                privilege: 'frosh_export.viewer'
            }
        },
        detail: {
            component: 'frosh-export-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'frosh.export.index',
                privilege: 'frosh_export.viewer'
            },
            props: {
                default(route) {
                    return {
                        froshExport: route.params.id
                    };
                }
            }
        },
        create: {
            component: 'frosh-export-create',
            path: 'create',
            meta: {
                parentPath: 'frosh.export.index',
                privilege: 'frosh_export.viewer'
            }
        }
    },

    navigation: [{
        path: 'frosh.export.index',
        privilege: 'frosh_export.viewer',
        label: 'frosh-export.general.mainMenuItemGeneral',
        id: 'frosh-export',
        parent: 'sw-catalogue',
        position: 90
    }]
});
