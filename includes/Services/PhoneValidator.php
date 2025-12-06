<?php

namespace Mygo\Services;

defined('ABSPATH') or die;

/**
 * Phone Validator
 * 
 * 統一的電話號碼驗證服務
 */
class PhoneValidator
{
    /**
     * 驗證台灣手機號碼
     * 
     * @param string $phone 電話號碼
     * @return array ['valid' => bool, 'sanitized' => string, 'error' => string]
     */
    public static function validate(string $phone): array
    {
        // 移除所有非數字字元
        $phoneClean = preg_replace('/[^\d]/', '', $phone);
        
        // 驗證格式：09 開頭，共 10 碼
        if (!preg_match('/^09\d{8}$/', $phoneClean)) {
            return [
                'valid' => false,
                'sanitized' => '',
                'error' => __('請輸入有效的手機號碼（09xxxxxxxx）', 'mygo-plus-one'),
            ];
        }
        
        return [
            'valid' => true,
            'sanitized' => $phoneClean,
            'error' => '',
        ];
    }
    
    /**
     * 格式化電話號碼顯示
     * 
     * @param string $phone 電話號碼
     * @param string $format 格式：'dash' (09xx-xxx-xxx) 或 'space' (09xx xxx xxx)
     * @return string 格式化後的電話號碼
     */
    public static function format(string $phone, string $format = 'dash'): string
    {
        $phoneClean = preg_replace('/[^\d]/', '', $phone);
        
        if (strlen($phoneClean) !== 10) {
            return $phone;
        }
        
        $separator = $format === 'space' ? ' ' : '-';
        
        return substr($phoneClean, 0, 4) . $separator . 
               substr($phoneClean, 4, 3) . $separator . 
               substr($phoneClean, 7, 3);
    }
    
    /**
     * 批次驗證多個電話號碼
     * 
     * @param array $phones 電話號碼陣列
     * @return array ['valid' => array, 'invalid' => array]
     */
    public static function validateBatch(array $phones): array
    {
        $valid = [];
        $invalid = [];
        
        foreach ($phones as $phone) {
            $result = self::validate($phone);
            if ($result['valid']) {
                $valid[] = $result['sanitized'];
            } else {
                $invalid[] = [
                    'phone' => $phone,
                    'error' => $result['error'],
                ];
            }
        }
        
        return [
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }
}
