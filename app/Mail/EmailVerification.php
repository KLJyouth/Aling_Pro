<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * 用户实例
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * 验证码
     *
     * @var string
     */
    public $code;

    /**
     * 验证令牌
     *
     * @var string
     */
    public $token;

    /**
     * 创建一个新的邮件实例
     *
     * @param \App\Models\User $user
     * @param string $code
     * @param string $token
     * @return void
     */
    public function __construct(User $user, $code, $token)
    {
        $this->user = $user;
        $this->code = $code;
        $this->token = $token;
    }

    /**
     * 构建邮件消息
     *
     * @return $this
     */
    public function build()
    {
        $appName = config("app.name");
        $verifyUrl = route("user.email.verify.token", $this->token);

        return $this->subject("{$appName} - 邮箱验证")
            ->view("emails.email-verification")
            ->with([
                "name" => $this->user->name,
                "code" => $this->code,
                "verifyUrl" => $verifyUrl,
                "appName" => $appName,
            ]);
    }
}
