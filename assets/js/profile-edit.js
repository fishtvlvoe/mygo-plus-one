/**
 * Profile Edit JavaScript
 * 處理個人資料編輯頁面的表單驗證
 */

(function() {
    'use strict';

    /**
     * 顯示錯誤訊息
     */
    function showError(input, message) {
        const errorEl = input.parentElement.querySelector('.error-msg');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
        input.style.borderColor = '#ff3b30';
    }

    /**
     * 驗證 email 格式
     */
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * 驗證電話格式
     */
    function validatePhone(phone) {
        const phoneClean = phone.replace(/[^\d]/g, '');
        return /^09\d{8}$/.test(phoneClean);
    }

    /**
     * 驗證地址
     */
    function validateAddress(address) {
        return address.length >= 10;
    }

    /**
     * 設定 LINE form 的 action 和參數
     */
    function setupLineForm(authUrl) {
        const form = document.getElementById('mygo-line-form');
        
        if (!form) {
            console.error('MYGO: mygo-line-form not found');
            return;
        }
        
        // 手動分割 URL
        const parts = authUrl.split('?');
        if (parts.length === 2) {
            form.action = parts[0];
            
            // 手動解析查詢參數（避免使用 URL 物件）
            const params = parts[1].split('&');
            params.forEach(function(param) {
                const keyValue = param.split('=');
                if (keyValue.length === 2) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = decodeURIComponent(keyValue[0]);
                    input.value = decodeURIComponent(keyValue[1]);
                    form.appendChild(input);
                }
            });
        }
        
        console.log('MYGO: Form action:', form.action);
        console.log('MYGO: Form inputs:', Array.from(form.querySelectorAll('input[type="hidden"]')).map(i => i.name + '=' + i.value));
    }

    /**
     * 處理表單提交
     */
    function handleFormSubmit(e, form, lineForm, defaultRedirectUrl) {
        e.preventDefault();
        
        // 驗證表單
        const email = form.querySelector('[name="email"]').value.trim();
        const phone = form.querySelector('[name="phone"]').value.trim();
        const address = form.querySelector('[name="address"]').value.trim();
        const shipping = form.querySelector('[name="shipping_method"]').value;
        
        let hasError = false;
        
        // 清除之前的錯誤
        form.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
        
        // 驗證 email
        if (!validateEmail(email)) {
            showError(form.querySelector('[name="email"]'), '請輸入有效的 email 地址');
            hasError = true;
        }
        
        // 驗證電話
        const phoneClean = phone.replace(/[^\d]/g, '');
        if (!validatePhone(phone)) {
            showError(form.querySelector('[name="phone"]'), '請輸入有效的手機號碼（09xxxxxxxx）');
            hasError = true;
        }
        
        // 驗證地址
        if (!validateAddress(address)) {
            showError(form.querySelector('[name="address"]'), '請輸入完整的地址（至少 10 個字元）');
            hasError = true;
        }
        
        // 驗證寄送方式
        if (!shipping) {
            showError(form.querySelector('[name="shipping_method"]'), '請選擇寄送方式');
            hasError = true;
        }
        
        if (hasError) {
            return false;
        }
        
        // 儲存資料到 sessionStorage
        sessionStorage.setItem('mygo_pre_login_data', JSON.stringify({
            email: email,
            phone: phoneClean,
            address: address,
            shipping_method: shipping
        }));
        
        // 儲存原始要訪問的頁面
        const urlParams = new URLSearchParams(window.location.search);
        const redirectTo = urlParams.get('redirect_to') || defaultRedirectUrl;
        sessionStorage.setItem('mygo_redirect_after_login', redirectTo);
        
        // 提交 form 導向 LINE 登入
        console.log('MYGO: Submitting form to LINE');
        lineForm.submit();
        return false;
    }

    /**
     * 初始化個人資料編輯表單
     */
    function initProfileEditForm(config) {
        const form = document.getElementById('mygo-pre-login-form');
        const lineForm = document.getElementById('mygo-line-form');
        
        if (!form || !lineForm) {
            return;
        }
        
        // 設定 LINE form
        if (config.authUrl) {
            setupLineForm(config.authUrl);
        }
        
        // 綁定表單提交事件
        lineForm.addEventListener('submit', function(e) {
            handleFormSubmit(e, form, lineForm, config.defaultRedirectUrl);
        });
    }

    // 當 DOM 載入完成後執行
    document.addEventListener('DOMContentLoaded', function() {
        // 檢查是否有 mygoProfileEdit 設定（由 PHP 傳入）
        if (typeof mygoProfileEdit !== 'undefined') {
            initProfileEditForm(mygoProfileEdit);
        }
    });

    // 將函數暴露到全域（供其他腳本使用）
    window.MygoProfileEdit = {
        init: initProfileEditForm
    };

})();
