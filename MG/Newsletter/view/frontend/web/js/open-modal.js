define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'jquery/ui'
], function ($, modal, $t) {
    'use strict';

    $.widget('mage.openModalSlimright', {
        options: {
            popUp: []
        },
        /** @inheritdoc */
        _create: function () {
            this._initModal();
            this._bind();
        },

        _bind: function() {

        },
        _initModal: function() {
            var self = this;
            var popups = self.getPopUp();
            $('.btn-slimright').click(function () {
                var dataPopup = $(this).attr('data-popup');
                if (popups.length) {
                    for (var i = 0; i < popups.length; i++) {
                        if (popups[i].id == dataPopup) {
                            popups[i].popup.openModal();
                        }
                    }
                }

            })
        },

        /**
         * @return {*}
         * @private
         */
        getPopUp: function () {
            var self = this;
            $('.content-popup').each(function() {
                var container =  $(this),
                    containerId = container.attr('id');
                var popup = modal({
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    modalClass: 'dl-modal-slim',
                    buttons: false
                }, container);
                self.options.popUp.push({id: containerId, popup: popup});
            });


            return self.options.popUp;
        },
    })

    return $.mage.openModalSlimright;
});
