import template from './sw-cms-el-config-banner-slider.html.twig';
import './sw-cms-el-config-banner-slider.scss';

const {Component, Mixin} = Shopware;

Component.register('sw-cms-el-config-banner-slider', {
    template,
    mixins: [Mixin.getByName('cms-element')],

    data() {
        console.log('test')
        return {
            mediaModalIsOpen: false,
            activeSlideIndex: null
        };
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('banner-slider');

            if (!this.element.config.sliderItems || !Array.isArray(this.element.config.sliderItems.value)) {
                this.$set(this.element.config, 'sliderItems', {
                    source: 'static',
                    value: [
                        {mediaId: null, text: 'Slide 1'},
                        {mediaId: null, text: 'Slide 2'},
                        {mediaId: null, text: 'Slide 3'}
                    ]
                });
            }
        },

        onOpenMediaModal(index) {
            this.activeSlideIndex = index;
            this.mediaModalIsOpen = true;
        },

        onCloseModal() {
            this.mediaModalIsOpen = false;
            this.activeSlideIndex = null;
        },

        onSelectionChanges(mediaEntity) {
            const media = mediaEntity[0];
            const idx = this.activeSlideIndex;

            this.$set(this.element.config.sliderItems.value[idx], 'mediaId', media.id);
            this.$emit('element-update', this.element);
            this.onCloseModal();
        },

        async onImageUpload({targetId}) {
            const idx = this.activeSlideIndex;
            const mediaEntity = await this.mediaRepository.get(targetId);

            this.$set(this.element.config.sliderItems.value[idx], 'mediaId', mediaEntity.id);
            this.$emit('element-update', this.element);
            this.onCloseModal();
        },

        onImageRemove(index) {
            this.$set(this.element.config.sliderItems.value[index], 'mediaId', null);
            this.$emit('element-update', this.element);
        }
    }
});
