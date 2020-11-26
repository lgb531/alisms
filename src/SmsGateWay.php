<?php

namespace leolei\Alisms;

use leolei\Alisms\Util\Sign;
use leolei\Alisms\Util\Http;

/**
 * 阿里大于短信发送类
 * Class SmsGateWay
 * @package leolei\Alisms
 * @author Leo.lei <346991581@qq.com>
 */
class SmsGateWay
{
    const GATEWAY = 'https://eco.taobao.com/router/rest';//https方式 也可用http方式

    private $params;

    /**
     * SmsGateWay constructor.
     */
    public function __construct()
    {
        $this->params = [
            'app_key' => config('alidayu.app_key'),
            'timestamp' => date("Y-m-d H:i:s", NOW_TIME),
            'format' => 'json',
            'v' => '2.0',
            'sign_method' => 'md5',
            'sms_free_sign_name' => config('alidayu.signature')
        ];
    }

    /**
     * 发送短信
     * @param $mobile
     * @param $data
     * @param $template
     * @return array|void
     */
    public function send($mobile, $data, $template)
    {
        $params = [];
        $params['method'] = 'alibaba.aliqin.fc.sms.num.send';
        $params['sms_type'] = 'normal';
        $params['sms_param'] = json_encode($data);
        $params['rec_num'] = $mobile;
        $params['sms_template_code'] = $template;
        $params = array_merge($this->params, $params);
        $params['sign'] = Sign::create($params);
        $rsp = Http::post(self::GATEWAY, $params);
        $rsp = json_decode($rsp, true);
        return self::check_error($rsp);
    }

    /**
     * 错误码匹配
     * @param $rsp
     * @return array
     */
    protected function check_error($rsp)
    {
        $errCode = [
            'isv.OUT_OF_SERVICE' => '短信未发送成功，请稍后重试',//业务停机	登陆www.alidayu.com充值
            'isv.PRODUCT_UNSUBSCRIBE' => '短信未发送成功，请稍后重试',//产品服务未开通	登陆www.alidayu.com开通相应的产品服务
            'isv.ACCOUNT_NOT_EXISTS' => '短信未发送成功，请稍后重试',//账户信息不存在 登陆www.alidayu.com完成入驻
            'isv.ACCOUNT_ABNORMAL' => '短信未发送成功，请稍后重试',//账户信息异常 联系技术支持
            'isv.SMS_TEMPLATE_ILLEGAL' => '短信未发送成功，请稍后重试',//模板不合法 登陆www.alidayu.com查询审核通过短信模板使用
            'isv.SMS_SIGNATURE_ILLEGAL' => '短信未发送成功，请稍后重试',//签名不合法 登陆www.alidayu.com查询审核通过的签名使用
            'isv.MOBILE_NUMBER_ILLEGAL' => '请填写正确手机号码',//手机号码格式错误 使用合法的手机号码
            'isv.MOBILE_COUNT_OVER_LIMIT' => '短信未发送成功，请稍后重试',//手机号码数量超过限制 批量发送，手机号码以英文逗号分隔，不超过200个号码
            'isv.TEMPLATE_MISSING_PARAMETERS' => '短信未发送成功，请稍后重试',//短信模板变量缺少参数 确认短信模板中变量个数，变量名，检查传参是否遗漏
            'isv.INVALID_PARAMETERS' => '短信未发送成功，请稍后重试',//参数异常 检查参数是否合法
            'isv.BUSINESS_LIMIT_CONTROL' => '短信尚在有效期内，不可重复发送',//触发业务流控限制 短信验证码，使用同一个签名，对同一个手机号码发送短信验证码，允许每分钟1条，累计每小时7条。 短信通知，使用同一签名、同一模板，对同一手机号发送短信通知，允许每天50条（自然日）。
            'isv.INVALID_JSON_PARAM' => '短信未发送成功，请稍后重试',//JSON参数不合法 JSON参数接受字符串值。例如{"code":"123456"}，不接收{"code":123456}
            'isv.SYSTEM_ERROR' => '短信未发送成功，请稍后重试',//
            'isv.BLACK_KEY_CONTROL_LIMIT' => '短信未发送成功，请稍后重试',//模板变量中存在黑名单关键字 如：阿里大鱼	黑名单关键字禁止在模板变量中使用，若业务确实需要使用，建议将关键字放到模板中，进行审核。
            'isv.PARAM_NOT_SUPPORT_URL' => '短信未发送成功，请稍后重试',//不支持url为变量 域名和ip请固化到模板申请中
            'isv.PARAM_LENGTH_LIMIT' => '短信未发送成功，请稍后重试',//变量长度受限 变量长度受限 请尽量固化变量中固定部分
            'isv.AMOUNT_NOT_ENOUGH' => '短信未发送成功，请稍后重试',//余额不足 因余额不足未能发送成功，请登录管理中心充值后重新发送
        ];
        if (isset($rsp['alibaba_aliqin_fc_sms_num_send_response']['result']['success']) && $rsp['alibaba_aliqin_fc_sms_num_send_response']['result']['success'] == 'true') {
            return ['status' => 1, 'info' => '短信发送成功'];
        } else {
            return ['status' => 0, 'info' => $errCode[$rsp['error_response']['sub_code']], 'code' => $rsp['error_response']['code']];
        }
    }
}
