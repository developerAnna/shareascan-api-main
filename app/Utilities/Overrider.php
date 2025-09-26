<?php

namespace App\Utilities;

class Overrider
{

    public static function load($type)
    {
        $method = 'load' . ucfirst($type);
        static::$method();
    }

    protected static function loadSettings()
    {

        // Email
        $email_protocol = get_options('mail_type', 'mail');
        config(['mail.default' => $email_protocol]);

        config(['mail.from.name' => get_options('from_name')]);
        config(['mail.from.address' => get_options('from_email')]);

        if ($email_protocol == 'smtp') {
            config(['mail.mailers.smtp.host' => get_options('smtp_host')]);
            config(['mail.mailers.smtp.port' => get_options('smtp_port')]);
            config(['mail.mailers.smtp.username' => get_options('smtp_username')]);
            config(['mail.mailers.smtp.password' => get_options('smtp_password')]);
            config(['mail.mailers.smtp.encryption' => get_options('smtp_encryption')]);
        }
    }
}
