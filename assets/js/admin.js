/**
 * MYGO +1 Admin Scripts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // 自動計算訂單金額
        function updateOrderTotal() {
            var unitPrice = parseFloat($('input[name="unit_price"]').val()) || 0;
            var quantity = parseInt($('input[name="quantity"]').val()) || 1;
            var total = unitPrice * quantity;
            $('#order-total').text(total.toLocaleString('zh-TW'));
        }

        $('input[name="unit_price"], input[name="quantity"]').on('input', updateOrderTotal);

        // 儲存訂單資訊
        $('#mygo-save-order-info').on('click', function() {
            var $btn = $(this);
            var $form = $('#mygo-order-form');
            
            $btn.prop('disabled', true).text('儲存中...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mygo_update_order_info',
                    nonce: mygoAdmin.nonce,
                    order_id: $form.find('input[name="order_id"]').val(),
                    product_id: $form.find('input[name="product_id"]').val(),
                    unit_price: $form.find('input[name="unit_price"]').val(),
                    quantity: $form.find('input[name="quantity"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('訂單資訊已更新', 'success');
                        if (response.data.total) {
                            $('#order-total').text(response.data.total.toLocaleString('zh-TW'));
                        }
                    } else {
                        showNotice(response.data || '更新失敗', 'error');
                    }
                },
                error: function() {
                    showNotice('網路錯誤，請稍後再試', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('儲存訂單資訊');
                }
            });
        });

        // 訂單狀態切換（支援新舊兩種樣式）
        $('.mygo-status-toggle input[type="checkbox"], .mygo-ios-switch input[type="checkbox"]').on('change', function() {
            var $checkbox = $(this);
            var orderId = $checkbox.data('order-id');
            var statusType = $checkbox.data('status-type');
            var value = $checkbox.is(':checked');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mygo_update_order_status',
                    nonce: mygoAdmin.nonce,
                    order_id: orderId,
                    status_type: statusType,
                    value: value
                },
                success: function(response) {
                    if (response.success) {
                        // 顯示成功訊息
                        showNotice('狀態已更新', 'success');
                    } else {
                        // 還原狀態
                        $checkbox.prop('checked', !value);
                        showNotice(response.data || '更新失敗', 'error');
                    }
                },
                error: function() {
                    // 還原狀態
                    $checkbox.prop('checked', !value);
                    showNotice('網路錯誤，請稍後再試', 'error');
                }
            });
        });

        // 儲存訂單備註
        $('#mygo-save-notes').on('click', function() {
            var $btn = $(this);
            var orderId = $btn.data('order-id');
            var notes = $('#mygo-order-notes').val();

            $btn.prop('disabled', true).text('儲存中...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mygo_save_order_notes',
                    nonce: mygoAdmin.nonce,
                    order_id: orderId,
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('備註已儲存，即將返回列表...', 'success');
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    } else {
                        showNotice(response.data || '儲存失敗', 'error');
                        $btn.prop('disabled', false).text('存檔');
                    }
                },
                error: function() {
                    showNotice('網路錯誤，請稍後再試', 'error');
                    $btn.prop('disabled', false).text('存檔');
                }
            });
        });

        // 儲存買家資訊
        $('#mygo-save-buyer-info').on('click', function() {
            var $btn = $(this);
            var $form = $('#mygo-order-form');
            
            $btn.prop('disabled', true).text('儲存中...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mygo_update_buyer_info',
                    nonce: mygoAdmin.nonce,
                    user_id: $form.find('input[name="user_id"]').val(),
                    buyer_name: $form.find('input[name="buyer_name"]').val(),
                    phone: $form.find('input[name="phone"]').val(),
                    address: $form.find('input[name="address"]').val(),
                    shipping_method: $form.find('select[name="shipping_method"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('買家資訊已更新', 'success');
                    } else {
                        showNotice(response.data || '更新失敗', 'error');
                    }
                },
                error: function() {
                    showNotice('網路錯誤，請稍後再試', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('儲存買家資訊');
                }
            });
        });

        // 刪除訂單（訂單詳情頁）
        $('#mygo-delete-order').on('click', function() {
            if (!confirm('確定要刪除此訂單嗎？此操作無法復原。')) {
                return;
            }

            var $btn = $(this);
            var orderId = $btn.data('order-id');

            $btn.prop('disabled', true).text('刪除中...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mygo_delete_order',
                    nonce: mygoAdmin.nonce,
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('訂單已刪除，即將返回列表...', 'success');
                        setTimeout(function() {
                            window.location.href = response.data.redirect;
                        }, 1000);
                    } else {
                        showNotice(response.data || '刪除失敗', 'error');
                        $btn.prop('disabled', false).text('🗑️ 刪除訂單');
                    }
                },
                error: function() {
                    showNotice('網路錯誤，請稍後再試', 'error');
                    $btn.prop('disabled', false).text('🗑️ 刪除訂單');
                }
            });
        });

        // 全選訂單
        $('#mygo-select-all-orders').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.mygo-order-checkbox').prop('checked', isChecked);
            updateSelectedCount();
        });

        // 單選訂單
        $(document).on('change', '.mygo-order-checkbox', function() {
            updateSelectedCount();
            
            // 更新全選狀態
            var totalCheckboxes = $('.mygo-order-checkbox').length;
            var checkedCheckboxes = $('.mygo-order-checkbox:checked').length;
            $('#mygo-select-all-orders').prop('checked', totalCheckboxes === checkedCheckboxes);
        });

        // 更新選中數量
        function updateSelectedCount() {
            var count = $('.mygo-order-checkbox:checked').length;
            $('#mygo-selected-count').text(count);
            $('#mygo-bulk-delete-orders').prop('disabled', count === 0);
        }

        // 訂單搜尋
        $('#mygo-search-btn').on('click', function() {
            var searchTerm = $('#mygo-order-search').val();
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('s', searchTerm);
            window.location.href = currentUrl.toString();
        });

        $('#mygo-order-search').on('keypress', function(e) {
            if (e.which === 13) {
                $('#mygo-search-btn').click();
            }
        });

        // 批次刪除訂單
        $('#mygo-bulk-delete-orders').on('click', function() {
            var selectedIds = [];
            $('.mygo-order-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                return;
            }

            if (!confirm('確定要刪除選中的 ' + selectedIds.length + ' 筆訂單嗎？此操作無法復原。')) {
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('刪除中...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mygo_bulk_delete_orders',
                    nonce: mygoAdmin.nonce,
                    order_ids: selectedIds
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('已刪除 ' + response.data.deleted + ' 筆訂單', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        showNotice(response.data || '刪除失敗', 'error');
                        $btn.prop('disabled', false).text('批次刪除 (0)');
                    }
                },
                error: function() {
                    showNotice('網路錯誤，請稍後再試', 'error');
                    $btn.prop('disabled', false).text('批次刪除 (0)');
                }
            });
        });

        // 顯示通知訊息
        function showNotice(message, type) {
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.mygo-admin-wrap').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });

})(jQuery);
