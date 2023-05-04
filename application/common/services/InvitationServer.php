<?php

namespace app\common\services;

use app\common\model\user\Invitation;
use think\Log;

/**
 * 用户推广码
 * Class InvitationServer
 * @package app\common\services
 */
class InvitationServer
{
    /**
     * 生成用户推广码
     * @param $user_id
     * @throws \Endroid\QrCode\Exception\InvalidPathException
     * @throws \OSS\Core\OssException
     * @throws \think\Exception
     * @return bool|false|int
     */
    public function saveQrCode($user_id)
    {
        $UserInvitation = new Invitation();

        // 自定义二维码配置
        $config = [
            'title'         => false,
            'title_content' => '',
            'file_name'     => './uploads/',
            'generate'      => 'writefile',
            'logo'          => false,
            'logo_url'      => '',
            'logo_size'     => 0,
        ];

        //生成推广码
        $code = $this->getInvitationCode();

        //生成推广二维码
        $qr_code = new QrCodeServer($config);
        $params  = config('site.invitation_link') . '?code=' . $code;
        $codeUrl = $qr_code->createServer($params);
        if ($codeUrl['success']) {
            $data = ['user_id' => $user_id, 'code' => $code, 'qr_code' => $codeUrl['data']['url']];
            return $UserInvitation->save($data);
        } else {
            Log::write('生成推广码失败，用户ID：' + $user_id + '；报错信息：' . $codeUrl['message']);
            return false;
        }
    }

    /**
     * 生成唯一推广码
     * @throws \think\Exception
     * @return string
     */
    protected function getInvitationCode()
    {
        $UserInvitation = new Invitation();
        //生成推广码
        $code = random(8, 'all');

        $count = $UserInvitation->where(['code' => $code])->count();
        if ($count > 0) {
            $code = $this->getInvitationCode();
        }
        return $code;
    }
}