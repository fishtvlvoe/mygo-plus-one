/**
 * MYGO +1 Public Scripts
 */

(function($) {
    'use strict';

    // LINE 內部瀏覽器偵測
    function detectLineInAppBrowser() {
        var ua = navigator.userAgent || navigator.vendor;
        return /Line/i.test(ua);
    }

    // 導向外部瀏覽器
    function redirectToExternalBrowser(targetUrl) {
        if (detectLineInAppBrowser()) {
            window.location.href = targetUrl + (targetUrl.indexOf('?') > -1 ? '&' : '?') + 'openExternalBrowser=1';
        }
    }

    // 顯示個人資料 Modal
    function showProfileModal(feedId, commentId) {
        $('#mygo-pending-feed').val(feedId || '');
        $('#mygo-pending-comment').val(commentId || '');
        $('#mygo-profile-modal').fadeIn(200);
    }

    // 隱藏 Modal
    function hideModal(modalId) {
        $(modalId).fadeOut(200);
    }

    // 顯示規格選擇 Modal
    function showVariantModal(feedId, productId, quantity, variants) {
        var $options = $('#mygo-variant-options');
        $options.empty();

        variants.forEach(function(variant) {
            $options.append(
                '<button type="button" class="mygo-variant-btn" data-variant="' + variant + '">' +
                variant +
                '</button>'
            );
        });

        $('#mygo-variant-feed').val(feedId);
        $('#mygo-variant-product').val(productId);
        $('#mygo-variant-quantity').val(quantity);
        $('#mygo-variant-modal').fadeIn(200);
    }

    $(document).ready(function() {
        // LINE 內部瀏覽器處理
        if (detectLineInAppBrowser()) {
            var needsAuth = $('body').hasClass('mygo-needs-auth');
            if (needsAuth) {
                redirectToExternalBrowser(window.location.href);
            }
        }

        // Modal 關閉
        $('.mygo-modal-close').on('click', function() {
            $(this).closest('.mygo-modal').fadeOut(200);
        });

        $('.mygo-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).fadeOut(200);
            }
        });

        // 個人資料表單提交
        $('#mygo-profile-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            
            $btn.prop('disabled', true).text('處理中...');
            $('.mygo-error').text('');

            $.ajax({
                url: mygoAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mygo_save_profile',
                    nonce: mygoAjax.nonce,
                    phone: $('#mygo-phone').val(),
                    address: $('#mygo-address').val(),
                    shipping_method: $('#mygo-shipping').val()
                },
                success: function(response) {
                    if (response.success) {
                        hideModal('#mygo-profile-modal');
                        alert('資料已儲存！');
                        location.reload();
                    } else {
                        if (response.data && response.data.errors) {
                            $.each(response.data.errors, function(field, message) {
                                $('#mygo-' + field + '-error').text(message);
                            });
                        } else {
                            alert(response.data.message || '儲存失敗');
                        }
                    }
                },
                error: function() {
                    alert('網路錯誤，請稍後再試');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('儲存並下單');
                }
            });
        });

        // 規格選擇
        $(document).on('click', '.mygo-variant-btn', function() {
            var variant = $(this).data('variant');
            var feedId = $('#mygo-variant-feed').val();
            var productId = $('#mygo-variant-product').val();
            var quantity = $('#mygo-variant-quantity').val();

            $(this).addClass('selected').siblings().removeClass('selected');

            $.ajax({
                url: mygoAjax.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mygo_select_variant',
                    nonce: mygoAjax.nonce,
                    feed_id: feedId,
                    product_id: productId,
                    variant: variant,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        hideModal('#mygo-variant-modal');
                        alert('訂單已建立！');
                        location.reload();
                    } else {
                        alert(response.data.message || '下單失敗');
                    }
                },
                error: function() {
                    alert('網路錯誤，請稍後再試');
                }
            });
        });
    });

    // 暴露給全域使用
    window.MygoUtils = {
        detectLineInAppBrowser: detectLineInAppBrowser,
        redirectToExternalBrowser: redirectToExternalBrowser,
        showProfileModal: showProfileModal,
        showVariantModal: showVariantModal,
        hideModal: hideModal
    };

})(jQuery);
