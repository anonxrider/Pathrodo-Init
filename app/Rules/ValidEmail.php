<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidEmail implements Rule
{
    /**
     * The list of blocked temporary email domains.
     *
     * @var array
     */
    protected $blockedDomains = [
        'tempmail.com',
        '10minutemail.com',
        'mailinator.com',
        'yopmail.com',
        'throwawaymail.com',
        'disposablemail.com',
        'maildrop.cc',
        'guerrillamail.com',
        'getnada.com',
        'mailnesia.com',
        'trashmail.com',
        'fakeinbox.com',
        'mailinator.net',
        'jetable.com',
        'spamgourmet.com',
        'dodgit.com',
        'mytemp.email',
        'sharklasers.com',
        'temp-mail.org',
        'mail-temporaire.fr',
        'boun.cr',
        'cybermail.com',
        'mailme.com',
        'mailc.net',
        'mailz.info',
        'binkmail.com',
        'e4ward.com',
        'myownemail.com',
        'mymail-inbox.com',
        'outlook.hu',
        'gustr.com',
        'mailnator.com',
        'mailcatch.com',
        'spambox.us',
        'spamshelter.com',
        'spammail.net',
        'spambox.xyz',
        'tempemail.co',
        'wegwerfemail.de',
        'yopmail.fr'
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $domain = substr(strrchr($value, "@"), 1);
        return !in_array($domain, $this->blockedDomains);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email address is from a blocked temporary email provider.';
    }
}

