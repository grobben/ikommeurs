/**
 * lotusbreath
 * All javasript function of checkout page
 * File opcheckout.js
 */
(function($, window) {
    'use strict';
    $.widget('lotusbreath.onestepcheckout', {
        options: {
            checkout: {
                loginFormSelector: '#login-form',
                continueSelector: '#lbonepage-place-order-btn',
                registerCustomerPasswordSelector: '#register-customer-password',
                suggestRegistration: false,
                checkoutForm : '#checkout_form'

            },
            submitUrl : ''
        },
        _create : function() {
            var _this = this;
            $("#checkout_form").validation();
            this._addDropdowCartQty();
            $(document).bind("ajaxStart", function(){
                $(".button:not(#apply_coupon_btn)").addClass('disabled');
                $(".button:not(#apply_coupon_btn)").attr('disabled', 'disabled');
            });
            $(document).bind("ajaxStop", function(){
                $(".button:not(#apply_coupon_btn)").removeClass('disabled');
                $(".button:not(#apply_coupon_btn)").removeAttr('disabled');
                _this._removeWait('checkoutSteps');
            });


            this.element
            .on('click', this.options.checkout.continueSelector, function(e) {
                    e.preventDefault();
                    $.proxy(_this._save($(this)), _this);
                    $(".mage-error").show();
                    return false;
                })
            var that = this;
            /*
            $( ".a-agreement").click(function(e){
                e.preventDefault();
                $( "#"+$(this).attr('rel') ).dialog({
                    modal: true,
                    width : 550,
                    appendTo: that.options.checkout.checkoutForm,
                    buttons: {
                        Ok: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });

            });
            */
            $( ".a-agreement").each(function(){
                $(this).magnificPopup(
                    {
                        items: {
                            type: 'inline',
                            src : "#"+$(this).attr('rel'),

                            prependTo: that.options.checkout.checkoutForm,
                        },
                        fixedContentPos : true
                        //modal: true
                    }
                );
            });

            /**
             * checkbox : is create new account
             */
            if ($("input[name=\"billing[create_new_account]\"]").checked)
                $(that.options.checkout.registerCustomerPasswordSelector).show();
            else
                $(that.options.checkout.registerCustomerPasswordSelector).hide();
            $("input[name=\"billing[create_new_account]\"]").click(function(){
                if (this.checked)
                    $(that.options.checkout.registerCustomerPasswordSelector).show();
                else
                    $(that.options.checkout.registerCustomerPasswordSelector).hide();
            });


            /**
             * dialog
             */
            /*$( "#loginFrm" ).dialog({
                    autoOpen: false,
                    appendTo: that.options.checkout.checkoutForm,
                    height: 'auto',
                    width: 280,
                    modal: true,
                    close : function(){
                        $("#billing\\:email").focus();
                    }
            });*/

            $("#loginFrmDlgO").magnificPopup(
                {
                    items: {
                        type: 'inline',
                        src : '#loginFrm',
                        prependTo: that.options.checkout.checkoutForm
                    }



                }

            );



            $( "#loginFrm").submit(function(event){
                event.preventDefault();
                if (( $(this).validation() && $(this).validation('isValid') )) {
                    $.ajax({
                        url : $(this).attr('action'),
                        type : 'POST',
                        data : $(this).serializeArray(),
                        beforeSend : function(){

                            $("#loginFrm .mage-error").html('');
                            _this._loadWait('loginFrm');
                        },
                        success : function(response){
                            var resultO = $.parseJSON (response);
                            if (resultO.success) {
                                window.location.reload();
                            }else {
                              $("#loginFrmErrMsg").html(resultO.messages[0]);
                            }
                            _this._removeWait('loginFrm');
                            //
                        }
                    });
                }
            });

            /**
             * checkbox address
             */
            //shipping-area
            $("input[name=\"billing[use_for_shipping]\"]").change(
                function(){
                    if ($("#billing\\:use_for_shipping_yes").length){
                        if ($("#billing\\:use_for_shipping_yes").is(":checked") == 1){
                            $("#shipping-area").hide();
                        } else {
                            $("#shipping-area").show();
                        }
                    }else{
                        $("#shipping-area").hide();
                    }

                }
            );
            $("input[name=\"billing[use_for_shipping]\"]").trigger('change');

        },

        _checkAgreements : function () {
            var agreements = this.element.find('[name^="agreement["]');
            if (agreements.length == 0) return true;
            if (agreements.length &&  agreements.filter('input:checkbox:checked').length == agreements.length) {
                return true;
            }
            $("#agreenment-error").html($.mage.__(this.options.termErrorMsg));
            return false;
        },
        _showError : function(errIdSel, message){
            $(errIdSel).html(message);
            var scrollPos = $(errIdSel).offset().top -$(errIdSel).height();
            $('html,body').animate({ scrollTop: scrollPos }, 500);
        },
        _removeWait: function(elID){
            //overlayBlock
            $("#"+elID).find('.spinner').remove();
            if ($("#overlayBlock").length){
                $("#overlayBlock").remove();
            }
        },
        _loadWait : function(elID , isOverlay){
            var _this = this;
            if (isOverlay){
                $('body').append('<div id="overlayBlock" class="blockUI blockOverlay" style="z-index: 1000; border: none; margin: 0px; padding: 0px; width: 100%; height: 100%; top: 0px; left: 0px; background-color: rgb(0, 0, 0); opacity: 0.6;  position: fixed;"></div>');
            }
            var opts = {
                lines: 15, // The number of lines to draw
                length: 3, // The length of each line
                width: 6, // The line thickness
                radius: 20, // The radius of the inner circle
                corners: 1, // Corner roundness (0..1)
                rotate: 0, // The rotation offset
                direction: 1, // 1: clockwise, -1: counterclockwise
                color: 'orange', // #rgb or #rrggbb or array of colors
                speed: 1, // Rounds per second
                trail: 60, // Afterglow percentage
                shadow: false, // Whether to render a shadow
                hwaccel: false, // Whether to use hardware acceleration
                className: 'spinner', // The CSS class to assign to the spinner
                zIndex: 2e9, // The z-index (defaults to 2000000000)
                top: 'auto', // Top position relative to parent in px
                left: 'auto' // Left position relative to parent in px
            };
            var target = document.getElementById(elID);
            var spinner = new Spinner(opts).spin(target);
        },

        _updateHtml : function(responseObject){
            var _this = this;
            if (responseObject.htmlUpdates) {
                for (var idx in responseObject.htmlUpdates) {
                    if (responseObject.update_items.indexOf(idx) >= 0){

                        $("#" + idx).html(responseObject.htmlUpdates[idx]);
                        if(idx == 'payment_partial'){
                            //$(_this.options.payment.divId + ' dt input:radio:checked').trigger('click');
                            _this._updateAfterReloadPayment();
                        }
                        if(idx == 'review_partial'){
                            this._addDropdowCartQty();
                            this._addRemoveCartEvent();
                        }

                    }
                }


            }
        },
        _openConfirmExistEmail : function(){
            var _this = this;
            $("#confirm_dialog .content").html(_this.options.billing.checkExistsMsg);

            $("#confirm_dialog .btn_ok").click(function(){
                $("#login-email").val($("#billing\\:email").val());
                var mfp = $.magnificPopup.instance;
                 mfp.items[0]=
                     {
                         type: 'inline',
                         src : '#loginFrm',
                         prependTo: _this.options.checkout.checkoutForm
                     }
                ;
                mfp.updateItemHTML();
                return false;

            }).find('.btn_text').html(_this.options.confirmCheckEmail.login_btn_text);

            $("#confirm_dialog .btn_cancel").click(function(){
                $.magnificPopup.close();
                $("#billing\\:email").focus();
            }).find('.btn_text').html(_this.options.confirmCheckEmail.cancel_btn_text);


            $.magnificPopup.open(
                {
                    items: {
                        type: 'inline',
                        src : '#confirm_dialog',
                        modal: true
                    },


                }

            );
        },

        _placeOrder : function(){
            var _this = this;
            var data = $("#checkout_form").serialize();
            var url = this.options.submitUrl;
            $.ajax({
                url: url,
                type: 'post',
                context: this,
                //contentType: "application/json; charset=utf-8",
                data: data,
                dataType: 'json',
                beforeSend: function(){
                    $(".error").html('');
                    _this._loadWait('checkoutSteps', true);
                },

                error : function(request, status, error){
                },
                complete: function(response) {
                    var responseObject = $.parseJSON(response.responseText);
                    var result = responseObject.results;
                    if (result.payment && result.payment.redirect){
                        window.location = result.payment.redirect;
                        return;
                    }
                    if (result.save_order && result.save_order.success == true){
                        var redirectUrl = this.options.review.successUrl;
                        if (result.save_order.redirect){
                            redirectUrl = result.save_order.redirect;
                        }

                        window.location = redirectUrl;
                    }
                    $("#saveOder-error").html();
                    if (result.save_order && result.save_order.error && result.save_order.error == true){
                        //error_messages
                        $("#saveOder-error").html(result.save_order.error_messages);
                    }
                    _this._updateHtml(responseObject);
                    _this._removeWait('checkoutSteps');
                    //errors handler
                    //billing-error
                    $(".mage-error").show();
                    if(result.billing && typeof(result.billing.error) != "undefined" && result.billing.error == 1){
                        _this._showError("#billing-error", result.billing.message);
                    }else {
                        $("#billing-error").html('');
                    }
                    //payment-error
                    if( result.payment && typeof(result.payment.error)!= "undefined" && result.payment.error == 1){
                        _this._showError("#payment-error", result.payment.message);
                    } else {
                        $("#payment-error").html('');
                    }
                }
            });
        },

        _save : function(elem) {
            var _this = this;

            if ($("#checkout_form").validation  && $("#checkout_form").validation('isValid')
                && this._validateShippingMethod() && this._validatePaymentMethod() && this._checkAgreements() ) {
                //$.blockUI();
                /*will be improved*/

                var isCheckExistEmail = $("input[name='billing[create_new_account]']").is(":checked") || $("#billing\\:email").hasClass('check-email-exists') ;
                var checkEmailOk = true;
                if (isCheckExistEmail){
                    //$("#billing\\:email").trigger('change');

                    checkEmailOk = false;
                    $.ajax({
                        url : _this.options.billing.checkExistsUrl,
                        type : 'POST',
                        context: this,
                        data : {email: $("#billing\\:email").val()},
                        //async : false,
                        beforeSend : function(){
                            //_this._loadWait('review_partial');
                            $(".error").html('');
                            _this._loadWait('checkoutSteps', true);
                        },
                        complete : function (response){
                            _this._removeWait('checkoutSteps');
                            var responseObject = $.parseJSON(response.responseText);
                            if (responseObject && responseObject.success == false){
                                _this._openConfirmExistEmail();
                                checkEmailOk = false;
                            }else{

                                _this._placeOrder();

                                checkEmailOk = true;
                            }
                            //_this._removeWait();
                        }

                    });

                }else{
                    _this._placeOrder();
                }
                if (checkEmailOk == false)
                    return;

            } else {

            }

        },
        /* Call when update location of address that cause to change shipping rates, shipping methods ,or payment  */
        _updateLocation : function(data, typeUpdate){
            //$.ajax()
            if (!typeUpdate)
                typeUpdate = 'shipping';
            var _this = this;
            var params = $("#checkout_form").serializeArray();
            if (typeUpdate == 'billing'){
                params[params.length] =  {'name' : 'step', 'value': 'update_location_billing'} ;
            }else{
                if (typeUpdate == 'billing_shipping'){
                    params[params.length] =  {'name' : 'step', 'value': 'update_location_billing_shipping'} ;
                }else{
                    params[params.length] =  {'name' : 'step', 'value': 'update_location'} ;
                }
            }


                //params.step = 'update_location_billing_shipping';

            /*for (var index in data) {
                params[index] = data[index];
            }*/

            $.ajax({
                url : _this.options.saveStepUrl,
                type : 'POST',
                data : params,
                beforeSend : function(){
                    if (typeUpdate == 'billing'){
                        _this._loadWait('payment_partial');
                    }
                    if (typeUpdate == 'billing_shipping'){
                        _this._loadWait('shipping_partial');
                        _this._loadWait('payment_partial');
                    }
                    if (typeUpdate == 'shipping'){
                        _this._loadWait('shipping_partial');
                    }
                    _this._loadWait('review_partial');
                },
                complete : function (response){
                    var responseObject = $.parseJSON(response.responseText);
                    _this._updateHtml(responseObject);

                }

            });
        }
        ,_ajaxSend : function () {

        }
        ,_ajaxComplete : function () {

        }
       

    });

    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        options: {
            payment: {
                continueSelector: '#payment-buttons-container .button',
                form: '#co-payment-form',
                divId : '#payment_partial',
                methodsContainer: '#checkout-payment-method-load',
                rewardPointsCheckBoxSelector: '#use-reward-points',
                customerBalanceCheckBoxSelector: '#use-customer-balance',
                freeInput: {
                    tmpl: '<input id="hidden-free" type="hidden" name="payment[method]" value="free">',
                    selector: '#hidden-free'
                }
            }
        },

        _create: function() {
            this._super();

            this.element

                .on('updateCheckoutPrice', $.proxy(function(event, data) {
                    if (data.price) {
                        this.checkoutPrice += data.price;
                    }
                    if (data.totalPrice) {
                        data.totalPrice = this.checkoutPrice;
                    }
                    if (this.checkoutPrice < this.options.minBalance) {
                        // Add free input field, hide and disable unchecked checkbox payment method and all radio button payment methods
                        this._disablePaymentMethods();
                    } else {
                        // Remove free input field, show all payment method
                        this._enablePaymentMethods();
                    }
                }, this))
                .on('contentUpdated', this.options.payment.divId, $.proxy(function() {
                    $(this.options.payment.form).find('dd [name^="payment["]').prop('disabled', true);
                    var checkoutPrice = this.element.find(this.options.payment.form).find('[data-checkout-price]').data('checkout-price');
                    $(this.options.payment.customerBalanceCheckBoxSelector)
                        .prop({'checked':this.options.customerBalanceSubstracted,
                            'disabled':false}).change().parent().show();
                    $(this.options.payment.rewardPointsCheckBoxSelector)
                        .prop({'checked':this.options.rewardPointsSubstracted,
                            'disabled':false}).change().parent().show();
                    if ($.isNumeric(checkoutPrice)) {
                        this.checkoutPrice = checkoutPrice;
                    }
                    if (this.checkoutPrice < this.options.minBalance) {
                        this._disablePaymentMethods();
                    } else {
                        this._enablePaymentMethods();
                    }
                }, this))
                .on('click', this.options.payment.divId + ' dt input:radio', $.proxy(this._paymentMethodHandler, this))
                .find(this.options.payment.form).validation();
                //$(this.options.payment.divId + ' dt input:radio:checked').trigger('click');
                this._updateAfterReloadPayment();

        },
        _updateAfterReloadPayment : function(){
            var methods = this.element.find('[name="payment[method]"]');
            if (methods.length == 1) {
                $(methods[0]).parent().parent().nextUntil('dt').find('ul').show().find('[name^="payment["]').prop('disabled', false);
            }else{
                if (methods.length > 1){
                    var _ele = methods.filter('input:radio:checked')
                    var parentsDl = _ele.closest('dl');
                    parentsDl.find('dt input:radio').prop('checked', false);
                    _ele.prop('checked', true);
                    parentsDl.find('dd ul').hide().find('[name^="payment["]').prop('disabled', true);

                    _ele.parent().nextUntil('dt').find('ul').show().find('[name^="payment["]').prop('disabled', false);
                }

            }
        },

        /**
         * Display payment details when payment method radio button is checked
         * @private
         * @param e
         */
        _paymentMethodHandler: function(e) {

            var _this = $(e.target),
                parentsDl = _this.closest('dl');
            parentsDl.find('dt input:radio').prop('checked', false);

            _this.prop('checked', true);
            parentsDl.find('dd ul').hide().find('[name^="payment["]').prop('disabled', true);

            _this.parent().nextUntil('dt').find('ul').show().find('[name^="payment["]').prop('disabled', false);
            _this.parent().nextUntil('dt').find('div').show().find('[name^="payment["]').prop('disabled', false);
            this._savePayment();
        },
        _savePayment : function() {
            var _this = this;
            var params = $("#payment_partial input").serializeArray();

            params[params.length] =  {'name' : 'step', 'value': 'payment_method'} ;
            $.ajax({
                url : _this.options.saveStepUrl,
                type : 'POST',
                data : params,
                beforeSend : function(){

                    _this._loadWait('review_partial');

                },
                complete : function (response){
                    var responseObject = $.parseJSON(response.responseText);
                    _this._updateHtml(responseObject);

                }

            });
        },

        /**
         * make sure one payment method is selected
         * @private
         * @return {Boolean}
         */
        _validatePaymentMethod: function() {
            $("#payment-error").html('');
            var methods = this.element.find('[name^="payment["]');
            if (methods.length === 0) {
                //alert($.mage.__('Your order cannot be completed at this time as there is no payment methods available for it.'));
                //$("#payment-error").html($.mage.__('Your order cannot be completed at this time as there is no payment methods available for it.'));
                this._showError("#payment-error", $.mage.__('Your order cannot be completed at this time as there is no payment methods available for it.'));
                return false;
            }
            if (this.checkoutPrice < this.options.minBalance) {
                return true;
            } else if (methods.filter('input:radio:checked').length) {
                return true;
            }
            this._showError("#payment-error", $.mage.__('Please specify payment method.'));

            return false;
        },

        /**
         * Disable and enable payment methods
         * @private
         */
        _disablePaymentMethods: function() {
            var paymentForm = $(this.options.payment.form);
            paymentForm.find('input[name="payment[method]"]').prop('disabled', true);
            paymentForm.find(this.options.payment.methodsContainer).hide().find('[name^="payment["]').prop('disabled', true);
            paymentForm.find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', true).parent().hide();
            paymentForm.find(this.options.payment.freeInput.selector).remove();
            $.tmpl(this.options.payment.freeInput.tmpl).appendTo(paymentForm);
        },

        /**
         * Enable and enable payment methods
         * @private
         */
        _enablePaymentMethods: function() {
            var paymentForm = $(this.options.payment.form);
            paymentForm.find('input[name="payment[method]"]').prop('disabled', false);
            paymentForm.find(this.options.payment.methodsContainer).show();
            paymentForm.find('input[id^="use"][name^="payment[use"]:not(:checked)').prop('disabled', false).parent().show();
            paymentForm.find(this.options.payment.freeInput.selector).remove();
        }
    });

    /**
     * Review and place order
     */
    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        options: {
            review: {
                showEditCartBtn : '#edit_cart_action',
                updateCartBtn : '#update_cart_action',
                cancelCartBtn : '#cancel_cart_action'
            }
        },

        _create: function() {
            this._super();
            var _this = this;

            this.element
                .on('click', this.options.review.showEditCartBtn, function(e){
                    e.preventDefault();
                    $(".spinner-qty").show();
                    $(".spinner-qty").parent().show();
                    $(_this.options.review.updateCartBtn).show();
                    $(_this.options.review.cancelCartBtn).show();
                    $(".remove-item-diplay").show();
                    this.hide();
                })
                .on('click', this.options.review.cancelCartBtn, function(e){
                    $(_this.options.review.showEditCartBtn).show();
                    $(_this.options.review.updateCartBtn).hide();
                    $(_this.options.review.cancelCartBtn).hide();
                    $(".spinner-qty").hide();
                    $(".spinner-qty").parent().hide();
                    $(".remove-item-diplay").hide();
                })
                .on('click', this.options.review.updateCartBtn, function(e){
                    //var params = $("#checkout-review-table-wrapper input").serializeArray();
                    var params = $("#checkout_form").serializeArray();
                    //params.step = 'payment_method';
                    $.ajax({
                        url : _this.options.review.updateCarUrl,
                        type : 'POST',
                        data : params,

                            beforeSend : function(){
                            _this._loadWait('review_partial');
                            _this._loadWait('shipping_partial');
                            _this._loadWait('payment_partial');
                        },
                        complete : function (response){
                            var responseObject = $.parseJSON(response.responseText);
                            _this._updateHtml(responseObject);

                        }

                    });
                })
                ;
                this._addRemoveCartEvent();
        },
        _addRemoveCartEvent : function(){
            var _this = this;
            $(".cart-remove-btn").click(function(e){
                e.preventDefault();
                var params = {id : $(this).attr('rel')};
                $.magnificPopup.open(
                    {
                        items: {
                            type: 'inline',
                            src : '#confirm_dialog',
                            modal: true
                        },
                        callbacks :{
                            open: function() {

                                $("#confirm_dialog .content").html(_this.options.review.corfirmRemoveCartItemMsg);
                                $("#confirm_dialog .btn_cancel").click(function(){
                                    $.magnificPopup.close();
                                });
                                $("#confirm_dialog .btn_ok").click(function(){
                                    $.magnificPopup.close();

                                    $.ajax({
                                        url : _this.options.review.clearCartItemUrl,
                                        type : 'POST',
                                        data : params,
                                        beforeSend : function(){
                                            _this._loadWait('review_partial');
                                            _this._loadWait('payment_partial');
                                            _this._loadWait('shipping_partial');
                                        },
                                        complete : function (response){
                                            var responseObject = $.parseJSON(response.responseText);
                                            if (responseObject.results == false){
                                                if (responseObject.cart_is_empty ){
                                                    window.location.reload();
                                                }
                                            }
                                            _this._updateHtml(responseObject);

                                        }

                                    });

                                })
                            },
                            close: function() {
                                // Will fire when popup is closed
                            }
                        }


                    }

                );
                /*
                if(confirm(_this.options.review.corfirmRemoveCartItemMsg)){

                }
                */
                return false;

            });
        },
        _addDropdowCartQty : function(){
            $( ".spinner-qty").each(function(){
                var maxQty = 100000;
                if ($(this).attr('data-max'))
                    maxQty = $(this).attr('data-max');

                $(this).spinner({min : 1, max: maxQty});

            })

        }
    });

    /*
       Billing
     */
    // Extension for mage.opcheckout - second section(Billing Information) in one page checkout accordion
    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        options: {
            billing: {
                addressDropdownSelector: '#billing-address-select',
                newAddressFormSelector: '#billing-new-address-form',
                continueSelector: '#billing-buttons-container .button',
                form: '#co-billing-form',
                countryDropdownSelector : '#billing\\:country_id',
                city : '#billing\\:city',
                region: '#billing\\:region',
                region_id: '#billing\\:region_id',
                useForShippingAddressCheckboxId : '#billing\\:use_for_shipping_yes',
                noUseForShippingAddressCheckboxId : '#billing\\:use_for_shipping_no'
            }
        },

        _onchangeBillingLocactionFields : function(){
            var _this = this;
            $(_this.options.billing.newAddressFormSelector+" .change_location_field").on('change', function(){
                if($(_this.options.billing.useForShippingAddressCheckboxId).is(':checked')
                    || _this.options.billing.alwaysUseShippingAsBilling
                    ){

                    _this._updateLocation(null, 'billing_shipping');
                } else {
                    _this._updateLocation(null, 'billing');
                }
                

            });
            if ($(_this.options.billing.countryDropdownSelector).hasClass('update-location-region-class')){
                $("#billing\\:region_id").on('change', function(){
                    if($(_this.options.billing.useForShippingAddressCheckboxId).is(':checked')
                        || _this.options.billing.alwaysUseShippingAsBilling
                        ){
                        _this._updateLocation(null, 'billing_shipping');
                    } else {
                        _this._updateLocation(null, 'billing');
                    }
                });
                $("#billing\\:region").on('change', function(){
                    if($(_this.options.billing.useForShippingAddressCheckboxId).is(':checked')
                        || _this.options.billing.alwaysUseShippingAsBilling
                        ){
                        _this._updateLocation(null, 'billing_shipping');
                    } else {
                        _this._updateLocation(null, 'billing');
                    }
                });
            }
        },
        _create: function() {
            this._super();
            var _this = this;
            /**
             * Validate email
             */

             //alert($.mage.__('Please specify payment method.'));
            //
            /*will be improved*/
            var isCheckExistEmail = $("input[name='billing[create_new_account]']").is(":checked") || $("#billing\\:email").hasClass('check-email-exists') ;

            if (isCheckExistEmail){
                $("#billing\\:email").trigger('change');
            }
            $("input[name='billing[create_new_account]']").on('change',function(){
                if($(this).is(":checked")){
                    $("#billing\\:email").trigger('change');
                }
            });
            $("#billing\\:email").bind('change', function(){
                var isCheckExistEmail = $("input[name='billing[create_new_account]']").is(":checked") || $("#billing\\:email").hasClass('check-email-exists') ;
                if (!isCheckExistEmail)
                    return;

                $.ajax({
                    url : _this.options.billing.checkExistsUrl,
                    type : 'POST',
                    data : {email: $("#billing\\:email").val()},
                    beforeSend : function(){
                        //_this._loadWait('review_partial');
                    },
                    complete : function (response){
                        var responseObject = $.parseJSON(response.responseText);
                        if (responseObject && responseObject.success == false){
                            _this._openConfirmExistEmail();

                        }

                    }

                });
            });





            _this._onchangeBillingLocactionFields();
            this.element
                .on('click', _this.options.billing.noUseForShippingAddressCheckboxId, function(){
                    /*var data = {
                        'country_id' : $(_this.options.shipping.countryDropdownSelector).val()
                    };*/

                    //_this._updateLocation(data, 'shipping');
                    $(_this.options.shipping.countryDropdownSelector) && $(_this.options.shipping.countryDropdownSelector).trigger('change');
                })
                .on('change', this.options.billing.addressDropdownSelector, $.proxy(function(e) {
                    this.element.find(this.options.billing.newAddressFormSelector).toggle(!$(e.target).val());
                    if($(_this.options.billing.useForShippingAddressCheckboxId).is(':checked')
                        || _this.options.billing.alwaysUseShippingAsBilling
                        ){
                        _this._updateLocation(null, 'billing_shipping');
                    } else {
                        _this._updateLocation(null, 'billing');
                    }
                }, this))
                .find(this.options.billing.form).validation();

            if (_this.options.autoDetectLocation){
                try{
                    $.getJSON("http://freegeoip.net/json/", function(result){
                        if (result.country_code){
                            $(_this.options.billing.countryDropdownSelector).val(result.country_code);

                        }
                        if (result.region_code){
                            $(_this.options.billing.region_id).val(result.region_code);
                        }
                        if (result.region_name){
                            $(_this.options.billing.region).val(result.region_name);
                        }
                        if (result.city){
                            $(_this.options.billing.city).val(result.city);
                        }
                        $(_this.options.billing.countryDropdownSelector) && $(_this.options.billing.countryDropdownSelector).trigger('change');

                        //shipping
                        if (result.country_code){
                            $(_this.options.shipping.countryDropdownSelector).val(result.country_code);

                        }
                        if (result.region_code){
                            $(_this.options.shipping.region_id).val(result.region_code);
                        }
                        if (result.region_name){
                            $(_this.options.shipping.region).val(result.region_name);
                        }
                        if (result.city){
                            $(_this.options.shipping.city).val(result.city);
                        }
                        if(!$(_this.options.billing.useForShippingAddressCheckboxId).is(':checked')
                            &&  !_this.options.billing.alwaysUseShippingAsBilling
                            ){
                            $(_this.options.shipping.countryDropdownSelector) && $(_this.options.shipping.countryDropdownSelector).trigger('change');
                        }


                    });
                }catch (e){

                }

            }
        }


    });

    //shipping
    // Extension for mage.opcheckout - third section(Shipping Information) in one page checkout accordion
    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        options: {
            shipping: {
                form: '#co-shipping-form',
                addressDropdownSelector: '#shipping-address-select',
                newAddressFormSelector: '#shipping-new-address-form',
                copyBillingSelector: '#shipping\\:same_as_billing',
                countrySelector: '#shipping\\:country_id',
                city : '#shipping\\:city',
                region: '#shipping\\:region',
                region_id: '#shipping\\:region_id',
                countryDropdownSelector : '#shipping\\:country_id',
                continueSelector:'#shipping-buttons-container .button'
            }
        },

        _onchangeShippingLocactionFields : function(){
            var _this = this;
            $(_this.options.shipping.newAddressFormSelector+" .change_location_field").on('change', function(){
                _this._updateLocation(null);
            });
            if ($(_this.options.shipping.countryDropdownSelector).hasClass('update-location-region-class')){
                $("#shipping\\:region_id").on('change', function(){
                    _this._updateLocation(null);

                });
                $("#shipping\\:region").on('change', function(){
                    _this._updateLocation(null);

                });
            }
        },
        _create: function() {
            this._super();
            var _this = this;
            this._onchangeShippingLocactionFields();
            this.element
                .on('change', _this.options.shipping.addressDropdownSelector, $.proxy(function(e) {
                    $(this.options.shipping.newAddressFormSelector).toggle(!$(e.target).val());
                    var data = {
                        'country_id' : $(_this.options.shipping.countrySelector).val()
                    };

                    _this._updateLocation(data);
                }, this))
                .on ('click', _this.options.billing.useForShippingAddressCheckboxId, function(e){
                    var data = {
                        'country_id' : $(_this.options.billing.countryDropdownSelector).val()
                    };

                    _this._updateLocation(data);
                })
                .on('input propertychange', this.options.shipping.form + ' :input[name]', $.proxy(function() {
                    $(this.options.shipping.copyBillingSelector).prop('checked', false);
                }, this))
                .on('click', this.options.shipping.copyBillingSelector, $.proxy(function(e) {
                    if ($(e.target).is(':checked')) {
                        this._billingToShipping();
                    }
                }, this))
                .find(this.options.shipping.form).validation();
        },

        /**
         * Copy billing address info to shipping address
         * @private
         */
        _billingToShipping: function() {
            $(':input[name]', this.options.billing.form).each($.proxy(function(key, value) {
                var fieldObj = $(value.id.replace('billing:', '#shipping\\:'));
                fieldObj.val($(value).val());
                if (fieldObj.is("select")) {
                    fieldObj.trigger('change');
                }
            }, this));
            $(this.options.shipping.copyBillingSelector).prop('checked', true);
        }
    });

    // Shipping method
    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        options: {
            shippingMethod: {
            }
        },
        _create: function() {
            this._super();
            var _this = this;

            this.element
                .on('click', 'input[name="shipping_method"]', function(e) {
                    /*var selectedPrice = _this.shippingCodePrice[$(this).val()] || 0,
                        oldPrice = _this.shippingCodePrice[_this.currentShippingMethod] || 0;
                    _this.checkoutPrice = _this.checkoutPrice - oldPrice + selectedPrice;
                    _this.currentShippingMethod = $(this).val();
                     {
                     'step' : 'shipping_method',
                     'shipping_method' : $(this).val(),
                     'params' :
                     },
                    */
                    var params = $("#checkout_form").serializeArray();
                    params[params.length] =  {'name' : 'step', 'value': 'shipping_method'} ;
                    params[params.length] =  {'name' : 'shipping_method', 'value':  $(this).val()} ;
                    $.ajax({
                        url : _this.options.saveStepUrl,
                        type : 'POST',
                        data : params,

                        beforeSend : function(){
                            $("#shippingmethod-error").html("");
                            _this._loadWait('review_partial');
                            _this._loadWait('payment_partial');
                        },
                        complete : function (response){

                            var responseObject = $.parseJSON(response.responseText);
                            _this._updateHtml(responseObject);
                        }

                    });
                })
                .on('contentUpdated', $.proxy(function() {
                    this.currentShippingMethod = this.element.find('input[name="shipping_method"]:checked').val();
                    this.shippingCodePrice = this.element.find('[data-shipping-code-price]').data('shipping-code-price');
                }, this))
                .find(this.options.shippingMethod.form).validation();
        },

        /**
         * Make sure at least one shipping method is selected
         * @return {Boolean}
         * @private
         */
        _validateShippingMethod: function() {
            var methods = this.element.find('[name="shipping_method"]');
            $("#shippingmethod-error").html('');
            if (methods.length === 0) {
                this._showError("#shippingmethod-error", $.mage.__('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
                //$("#shippingmethod-error").html();
                return true;
            }
            if (methods.filter(':checked').length) {
                return true;
            }
            this._showError("#shippingmethod-error", $.mage.__('Please specify shipping method.'));

            //$("#shippingmethod-error").html($.mage.__('Please specify shipping method.'));
            return false;
        }
    });
    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        options : {
            coupon : {
                applyCouponBtn : "#apply_coupon_btn",
                cancelCouponBtn : "#cancel_coupon_btn",
                couponInput : "#coupon_code"
            }
        },
        _submitCoupon : function(isRemove){
            var _this = this;
            var params = $("#checkout_form").serializeArray();
            params[params.length] =  {'name' : 'coupon_code', 'value': $(_this.options.coupon.couponInput).val()} ;
            params[params.length] =  {'name' : 'remove', 'value': ((isRemove) ? 1  : 0)  } ;
            $.ajax({
                url : _this.options.coupon.applyCouponUrl,
                type : 'POST',
                data : params,
                beforeSend : function(){
                    //$("#shippingmethod-error").html("");
                    _this._loadWait('review_partial');
                    _this._loadWait('payment_partial');
                    _this._loadWait('shipping_partial');
                    $("#coupon-success-msg").html('');
                    $("#coupon-error-msg").html('');
                },
                complete : function (response){
                    var responseObject = $.parseJSON(response.responseText);
                    _this._updateHtml(responseObject);
                    if (responseObject.results.success == true){
                        $("#coupon-success-msg").html(responseObject.results.message);
                        if(isRemove) {
                            $(_this.options.coupon.cancelCouponBtn).addClass('hidden');
                            $(_this.options.coupon.couponInput).val('')
                        }else{
                            $(_this.options.coupon.cancelCouponBtn).removeClass('hidden');

                        }
                        $(_this.options.coupon.applyCouponBtn).addClass('disabled');
                        $(_this.options.coupon.applyCouponBtn).attr('disabled', 'disabled');
                    }
                    if (responseObject.results.success == false){
                        $("#coupon-error-msg").html(responseObject.results.message);
                    }

                }

            });
        },
        _create : function() {
            this._super();
            var _this = this;
            this.element
                .on( 'click', _this.options.coupon.applyCouponBtn  ,
                function (event){
                    _this._submitCoupon();

                })
                .on ('click', _this.options.coupon.cancelCouponBtn ,function(){
                    //_this.options.coupon.cancelCouponBtn
                    _this._submitCoupon(true);
                })
                .on ('keyup', _this.options.couponInput, function(){
                    var btnApply = $(_this.options.coupon.applyCouponBtn);

                    if (!$("#coupon_code").val()) {
                        btnApply.addClass('disabled');
                        btnApply.attr('disabled', 'disabled');
                    }else {
                        btnApply.removeClass('disabled');
                        //btnApply.attr('disabled', '');
                        btnApply.removeAttr('disabled');
                    }
                })
            ;
        }

    });
    /*
    $.widget('lotusbreath.onestepcheckout', $.lotusbreath.onestepcheckout, {
        _create: function() {
            this._super();
            var _this = this;
            $.magnificPopup.open(
                {
                    items: {
                        type: 'inline',
                        src : '#lb_osc_login',
                        modal: true
                    },
                    //modal: true

                }

            );
            onepageLogin = function(){
                var form = $("#lb_osc_login_frm");
                if (( form.validation() && form.validation('isValid') )) {
                    $.ajax({
                        url : _this.options.login.loginUrl,
                        type : 'POST',
                        data : form.serializeArray(),
                        beforeSend : function(){

                            $("#loginFrm .mage-error").html('');
                            _this._loadWait('loginFrm');
                        },
                        success : function(response){
                            var resultO = $.parseJSON (response);
                            if (resultO.success) {
                                window.location.reload();
                            }else {
                                $("#loginFrmErrMsg").html(resultO.messages[0]);
                            }
                            _this._removeWait('loginFrm');
                            //
                        }
                    });
                }
            }
        }
    });
    */



})(jQuery, window);
