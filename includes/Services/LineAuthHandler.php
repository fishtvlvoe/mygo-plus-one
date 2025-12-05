<?php

namespace Mygo\Services;

defined('ABSPATH') or die;

use Mygo\Contracts\LineAuthHandlerInterface;

/**
 * LINE Auth Handler
 * 
 * 處理 LINE Login OAuth 流程與使用者資料同步
 */
class LineAuthHandler implements LineAuthHandlerInterface
{
    private const LINE_AUTH_URL = 'https://access.line.me/oauth2/v2.1/authorize';
    private const LINE_TOKEN_URL = 'https://api.line.me/oauth2/v2.1/token';
    private const LINE_PROFILE_URL = 'https://api.line.me/v2/profile';

    /**
     * 取得 LINE Login 授權 URL
     */
    public function getAuthUrl(string $redirectUri): string
    {
        $clientId = get_option('mygo_line_login_channel_id', '');
        
        // 除錯訊息
        error_log('MYGO LineAuth: getAuthUrl - clientId = ' . $clientId);
        error_log('MYGO LineAuth: getAuthUrl - redirectUri = ' . $redirectUri);
        
        if (empty($clientId)) {
            error_log('MYGO LineAuth: getAuthUrl - ERROR: clientId is empty!');
        }
        
        $state = wp_create_nonce('mygo_line_login');

        // 儲存 state 供驗證
        set_transient('mygo_line_state_' . $state, true, 600);

        $params = [
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'openid',
        ];

        return self::LINE_AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * 處理 OAuth Callback
     */
    public function handleCallback(string $code, string $redirectUri = ''): array
    {
        // 取得 Access Token
        $tokenData = $this->getAccessToken($code, $redirectUri);
        
        if (!$tokenData || !isset($tokenData['access_token'])) {
            return [
                'success' => false,
                'error' => '無法取得 Access Token',
            ];
        }

        // 解析 ID Token（openid scope 就能取得基本資料）
        $idTokenData = null;
        if (isset($tokenData['id_token'])) {
            $idTokenData = $this->decodeIdToken($tokenData['id_token']);
            error_log('MYGO LineCallback: ID Token decoded - sub = ' . ($idTokenData['sub'] ?? 'none'));
        }
        
        if (!$idTokenData || !isset($idTokenData['sub'])) {
            error_log('MYGO LineCallback: ID Token missing or invalid');
            return [
                'success' => false,
                'error' => '無法取得使用者資料',
            ];
        }

        // 嘗試取得完整 profile（如果有 profile scope）
        $profile = $this->getProfile($tokenData['access_token']);
        
        $displayName = $profile['displayName'] ?? $idTokenData['name'] ?? 'LINE User';
        error_log('MYGO LineCallback: Display name = ' . $displayName);
        
        return [
            'success' => true,
            'profile' => [
                'userId' => $idTokenData['sub'], // sub 就是 LINE user ID
                'displayName' => $displayName,
                'pictureUrl' => $profile['pictureUrl'] ?? $idTokenData['picture'] ?? '',
                'email' => $idTokenData['email'] ?? null,
            ],
        ];
    }

    /**
     * 取得 Access Token
     */
    private function getAccessToken(string $code, string $redirectUri = ''): ?array
    {
        $clientId = get_option('mygo_line_login_channel_id', '');
        $clientSecret = get_option('mygo_line_login_channel_secret', '');
        
        if (empty($redirectUri)) {
            $redirectUri = rest_url('mygo/v1/line-callback');
        }

        $response = wp_remote_post(self::LINE_TOKEN_URL, [
            'body' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * 取得使用者 Profile
     */
    private function getProfile(string $accessToken): ?array
    {
        $response = wp_remote_get(self::LINE_PROFILE_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }

    /**
     * 解碼 ID Token
     */
    private function decodeIdToken(string $idToken): array
    {
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            return [];
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'));
        return json_decode($payload, true) ?: [];
    }

    /**
     * 同步使用者資料至 WordPress
     */
    public function syncUserData(array $lineProfile, int $wpUserId): void
    {
        update_user_meta($wpUserId, '_mygo_line_uid', $lineProfile['userId']);
        update_user_meta($wpUserId, '_mygo_line_name', $lineProfile['displayName']);
        
        if (!empty($lineProfile['email'])) {
            update_user_meta($wpUserId, '_mygo_line_email', $lineProfile['email']);
        }

        if (!empty($lineProfile['pictureUrl'])) {
            update_user_meta($wpUserId, '_mygo_line_picture', $lineProfile['pictureUrl']);
        }

        // 設定預設角色
        $existingRole = get_user_meta($wpUserId, '_mygo_role', true);
        if (empty($existingRole)) {
            update_user_meta($wpUserId, '_mygo_role', 'buyer');
        }

        // 同步至 FluentCRM（如果已啟用）
        $this->syncToFluentCRM($lineProfile, $wpUserId);

        do_action('mygo/user/synced', $wpUserId, $lineProfile);
    }

    /**
     * 同步至 FluentCRM
     */
    private function syncToFluentCRM(array $lineProfile, int $wpUserId): void
    {
        if (!function_exists('FluentCrmApi')) {
            return;
        }

        $user = get_userdata($wpUserId);
        if (!$user) {
            return;
        }

        $email = $lineProfile['email'] ?: $user->user_email;
        if (empty($email)) {
            return;
        }

        $contactApi = FluentCrmApi('contacts');
        
        $contactData = [
            'email' => $email,
            'first_name' => $lineProfile['displayName'],
            'custom_values' => [
                'line_uid' => $lineProfile['userId'],
            ],
            'tags' => ['line-user'],
        ];

        // 檢查是否已存在
        $existingContact = $contactApi->getContact($email);
        
        if ($existingContact) {
            $contactApi->updateContact($existingContact->id, $contactData);
        } else {
            $contactApi->createOrUpdate($contactData);
        }
    }

    /**
     * 偵測 LINE 內部瀏覽器
     */
    public function detectLineInAppBrowser(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return (bool) preg_match('/Line/i', $userAgent);
    }

    /**
     * 取得外部瀏覽器 URL
     */
    public function getExternalBrowserUrl(string $targetUrl): string
    {
        // 使用 LINE 的 openExternalBrowser 參數
        $separator = strpos($targetUrl, '?') !== false ? '&' : '?';
        return $targetUrl . $separator . 'openExternalBrowser=1';
    }

    /**
     * 處理登入或註冊
     */
    public function loginOrRegister(array $lineProfile): array
    {
        $validator = new UserProfileValidator();
        
        // 檢查是否已有綁定的使用者
        $existingUserId = $validator->findUserByLineUid($lineProfile['userId']);
        
        if ($existingUserId) {
            // 更新資料並登入
            $this->syncUserData($lineProfile, $existingUserId);
            wp_set_current_user($existingUserId);
            wp_set_auth_cookie($existingUserId);
            
            // 檢查資料是否完整
            $profileValidator = new UserProfileValidator();
            $profileCheck = $profileValidator->validateForOrder($existingUserId);
            $needsProfile = !$profileCheck['valid'];
            
            return [
                'success' => true,
                'user_id' => $existingUserId,
                'is_new' => false,
                'needs_profile' => $needsProfile,
            ];
        }

        // 建立新使用者
        // 使用 LINE 名稱作為基礎，移除特殊字元
        $displayName = $lineProfile['displayName'];
        $sanitizedName = sanitize_user(str_replace(' ', '_', $displayName), true);
        
        // 如果清理後的名稱為空或太短，使用 LINE UID 的一部分
        if (empty($sanitizedName) || strlen($sanitizedName) < 3) {
            $sanitizedName = 'line_user_' . substr($lineProfile['userId'], -8);
        }
        
        // 確保使用者名稱唯一
        $username = $sanitizedName;
        $counter = 1;
        while (username_exists($username)) {
            $username = $sanitizedName . '_' . $counter;
            $counter++;
        }
        
        // Email：優先使用 LINE 提供的，否則使用唯一的假 email
        $email = $lineProfile['email'] ?: $username . '@line.mygo.local';
        
        $userId = wp_create_user($username, wp_generate_password(20, true, true), $email);
        
        if (is_wp_error($userId)) {
            return [
                'success' => false,
                'error' => $userId->get_error_message(),
            ];
        }

        // 更新顯示名稱（使用原始的 LINE 名稱）
        wp_update_user([
            'ID' => $userId,
            'display_name' => $displayName,
            'first_name' => $displayName,
        ]);

        // 同步資料
        $this->syncUserData($lineProfile, $userId);

        // 登入
        wp_set_current_user($userId);
        wp_set_auth_cookie($userId);

        // 新用戶一定需要完善資料
        return [
            'success' => true,
            'user_id' => $userId,
            'is_new' => true,
            'needs_profile' => true,
        ];
    }
}
