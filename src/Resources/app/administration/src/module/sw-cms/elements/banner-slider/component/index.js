import template from './sw-cms-el-component-banner-slider.html.twig';
import './sw-cms-el-component-banner-slider.scss';

const {Component} = Shopware;

Component.register('sw-cms-el-component-banner-slider', {
    template,
    props: {
        element: {
            type: Object,
            required: true
        }
    }
});