import './component';
import './config';
import './preview';

Shopware.Service('cmsService').registerCmsElement({
    name: 'banner-slider',
    label: 'Banner Slider',
    component: 'sw-cms-el-component-banner-slider',
    configComponent: 'sw-cms-el-config-banner-slider',
    previewComponent: 'sw-cms-el-preview-banner-slider',
    defaultConfig: {
        sliderItems: {
            source: 'static',
            value: [
                {mediaId: null, text: 'Slide 1'},
                {mediaId: null, text: 'Slide 2'},
                {mediaId: null, text: 'Slide 3'}
            ]
        }
    }
});