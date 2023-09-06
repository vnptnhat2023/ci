<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

// Validation language settings
return [
	// Red2horse
	'ci_captcha' => 'Sai mã xác nhận.',
	'valid_json' => '{field} không đúng định dạng "json".',
	'is_array' => '{field} phải chứa đúng định dạng mảng một chiều.',
	'isAssoc' => '{field} phải chứa đúng định dạng mảng hai chiều.',
	'inPermission' => '{field} không chứa trong danh sách quyền.',

    // Core Messages
    'noRuleSets'      => 'Không có quy tắc nào được chỉ định trong cấu hình Xác thực.',
    'ruleNotFound'    => '{0} không phải là một quy tắc hợp lệ.',
    'groupNotFound'   => '{0} không phải là một nhóm quy tắc xác nhận.',
    'groupNotArray'   => '{0} nhóm quy tắc phải là một mảng.',
    'invalidTemplate' => '{0} không phải là mẫu Xác thực hợp lệ.',

    // Rule Messages
    'alpha'                 => 'Ô {field} chỉ có thể chứa các ký tự chữ cái.',
    'alpha_dash'            => 'Ô {field} chỉ có thể chứa các ký tự chữ và số, gạch dưới và dấu gạch ngang.',
    'alpha_numeric'         => 'Ô {field} chỉ có thể chứa các ký tự chữ và số.',
    'alpha_numeric_punct'   => 'Ô {field} chỉ có thể chứa các ký tự chữ và số, dấu cách và ký tự ~! # $% & * - _ + = | :. ',
    'alpha_numeric_space'   => 'Ô {field} chỉ có thể chứa các ký tự chữ và số.',
    'alpha_space'           => 'Ô {field} chỉ có thể chứa các ký tự chữ cái and spaces.',
    'decimal'               => 'Ô {field} phải chứa một số thập phân.',
    'differs'               => 'Ô {field} phải khác với {param}.',
    'equals'                => 'Ô {field} phải chính xác: {param}.',
    'exact_length'          => 'Ô {field} phải có độ dài chính xác {param} ký tự.',
    'greater_than'          => 'Ô {field} phải chứa một số lớn hơn {param}.',
    'greater_than_equal_to' => 'Ô {field} phải chứa một số lớn hơn hoặc bằng {param}.',
    'hex'                   => 'Ô {field} chỉ có thể chứa các ký tự thập lục phân.',
    'in_list'               => 'Ô {field} phải là một trong: {param}.',
    'integer'               => 'Ô {field} phải chứa một số nguyên.',
    'is_natural'            => 'Ô {field} chỉ được chứa các chữ số.',
    'is_natural_no_zero'    => 'Ô {field} chỉ được chứa các chữ số và phải lớn hơn 0.',
    'is_not_unique'         => 'Ô {field} phải chứa một giá trị hiện có trước đó trong cơ sở dữ liệu.',
    'is_unique'             => 'Ô {field} phải chứa một giá trị duy nhất.',
    'less_than'             => 'Ô {field} phải chứa một số nhỏ hơn {param}.',
    'less_than_equal_to'    => 'Ô {field} phải chứa một số nhỏ hơn hoặc bằng {param}.',
    'matches'               => 'Ô {field} không khớp với {param}.',
    'max_length'            => 'Ô {field} không thể vượt quá {param} ký tự.',
    'min_length'            => 'Ô {field} phải có ít nhất {param} ký tự.',
    'not_equals'            => 'Ô {field} không thể là: {param}.',
    'numeric'               => 'Ô {field} chỉ được chứa số.',
    'regex_match'           => 'Ô {field} không đúng định dạng',
    'required'              => 'Ô {field} bắt buộc.',
    'required_with'         => 'Ô {field} bắt buộc khi {param} tồn tại.',
    'required_without'      => 'Ô {field} bắt buộc khi {param} không tồn tại.',
    'timezone'              => 'Ô {field} phải là múi giờ hợp lệ. ',
    'valid_base64'          => 'Ô {field} phải là một chuỗi base64 hợp lệ.',
    'valid_email'           => 'Ô {field} phải là địa chỉ email hợp lệ.',
    'valid_emails'          => 'Ô {field} phải chứa tất cả các địa chỉ email hợp lệ.',
    'valid_ip'              => 'Ô {field} phải là một địa chỉ IP hợp lệ.',
    'valid_url'             => 'Ô {field} phải là một đường dẫn URL hợp lệ.',
    'valid_url_strict'      => 'Ô {field} phải là một đường dẫn URL hợp lệ.',
    'valid_date'            => 'Ô {field} phải là một ngày hợp lệ.',

    // Credit Cards
    'valid_cc_num' => '{field} dường như không phải là số thẻ tín dụng hợp lệ.',

    // Files
    'uploaded' => '{field} không phải là một tập tin tải lên hợp lệ.',
    'max_size' => '{field} tệp quá nặng.',
    'is_image' => '{field} không phải là một tập tin hình ảnh được tải lên hợp lệ.',
    'mime_in'  => '{field} phải là một tập tin có định dạng hợp lệ.',
    'ext_in'   => '{field} phải là phần mở rộng tập tin hợp lệ.',
    'max_dims' => '{field} không phải là một hình ảnh, hoặc nó quá rộng hoặc quá cao.',
];