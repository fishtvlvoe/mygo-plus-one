/**
 * LINE Login Page JavaScript
 * 處理 LINE 登入頁面的表單驗證和資料儲存
 */

(function() {
    'use strict';

    /**
     * 設定 LINE form 的 action 和參數
     */
    function setupLineForm(authUrl) {
        const lineForm = document.getElementById('mygo-line-form');
        
        if (!lineForm) {
            console.error('MYGO: mygo-line-form not found');
            return;
        }
        
        // 手動分割 URL
        const parts = authUrl.split('?');
        if (parts.length === 2) {
            lineForm.action = parts[0];
            
            // 手動解析查詢參數
            const params = parts[1].split('&');
            params.forEach(function(param) {
                const keyValue = param.split('=');
                if (keyValue.length === 2) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = decodeURIComponent(keyValue[0]);
                    input.value = decodeURIComponent(keyValue[1]);
                    lineForm.appendChild(input);
                }
            });
            
            console.log('MYGO: Form action set to:', lineForm.action);
        }
    }

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
     * 處理表單提交
     */
    function handleFormSubmit(e, form, lineForm, redirectUrl) {
        e.preventDefault();
        
        const email = form.querySelector('[name="email"]').value.trim();
        const phone = form.querySelector('[name="phone"]').value.trim();
        const address = form.querySelector('[name="address"]').value.trim();
        const shipping = form.querySelector('[name="shipping_method"]').value;
        
        let hasError = false;
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
        
        if (hasError) return false;
        
        const loginData = {
            email: email,
            phone: phoneClean,
            address: address,
            shipping_method: shipping
        };
        
        // 儲存到 sessionStorage
        sessionStorage.setItem('mygo_pre_login_data', JSON.stringify(loginData));
        
        // 同時儲存到 cookie（作為備份，因為 sessionStorage 可能在跨域時失效）
        document.cookie = 'mygo_pre_login_data=' + encodeURIComponent(JSON.stringify(loginData)) + '; path=/; max-age=600';
        
        sessionStorage.setItem('mygo_redirect_after_login', redirectUrl);
        
        console.log('MYGO: Data saved to sessionStorage and cookie');
        
        // 提交 form 導向 LINE
        lineForm.submit();
        return false;
    }

    /**
     * 初始化登入頁面
     */
    function initLineLoginPage(config) {
        const form = document.getElementById('mygo-pre-login-form');
        const lineForm = document.getElementById('mygo-line-form');
        
        if (!lineForm) {
            console.error('MYGO: mygo-line-form not found in DOM');
            return;
        }
        
        // 設定 LINE form
        if (config.authUrl) {
            setupLineForm(config.authUrl);
        }
        
        // 綁定表單提交事件
        lineForm.addEventListener('submit', function(e) {
            handleFormSubmit(e, form, lineForm, config.redirectUrl);
        });
    }

    /**
     * 初始化登入成功頁面
     */
    function initLoginSuccessPage(redirectUrl) {
        console.log('MYGO Debug: 資料已在伺服器端儲存');
        console.log('MYGO Debug: redirectUrl =', redirectUrl);
        
        // 清除 sessionStorage 和 cookie
        sessionStorage.removeItem('mygo_pre_login_data');
        sessionStorage.removeItem('mygo_redirect_after_login');
        document.cookie = 'mygo_pre_login_data=; path=/; max-age=0';
        
        // 延遲 1.5 秒後導向
        setTimeout(function() {
            window.location.href = redirectUrl;
        }, 1500);
    }

    // 當 DOM 載入完成後執行
    document.addEventListener('DOMContentLoaded', function() {
        // 檢查是否有 mygoLineLogin 設定（由 PHP 傳入）
        if (typeof mygoLineLogin !== 'undefined') {
            if (mygoLineLogin.pageType === 'login') {
                initLineLoginPage(mygoLineLogin);
            } else if (mygoLineLogin.pageType === 'success') {
                initLoginSuccessPage(mygoLineLogin.redirectUrl);
            }
        }
    });

    // 將函數暴露到全域（供其他腳本使用）
    window.MygoLineLogin = {
        init: initLineLoginPage,
        initSuccess: initLoginSuccessPage
    };

})();
