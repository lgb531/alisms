# 阿里大鱼短信库 ThinkPHP5 (重新修改版)

## 安装方法
```
composer require leolei/alisms
```

>类库使用的命名空间为`\\leolei\\Alisms`

## 配置说明
>ThinkPHP5格式用法
```php
<?php

return [
    'alidayu' =>[
        'app_key'=>'',
        'app_secret'=>'',
        'signature'=>'',
    ]
];
```
>使用ThinkPHP5配置格式进行加载

## 典型用法
>以ThinkPHP5为例

```php
<?php

namespace app\common\service;

use leolei\Alisms\SmsGateWay;

class Sms {

    public function send_code($mobile) {
        $code = mt_rand(1000, 9999);
        $AliSMS = new SmsGateWay();
        $AliSMS->send($mobile, ['code'=>$code], 'SMS_10210103');
    }

}

```
