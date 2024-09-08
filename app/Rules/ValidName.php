<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidName implements Rule
{
    /**
     * List of blocked keywords.
     *
     * @var array
     */
    protected $blockedKeywords = [
        'sex', 
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
        $valueLower = strtolower($value);
        
        // Check if the name contains any blocked keywords
        foreach ($this->blockedKeywords as $keyword) {
            if (strpos($valueLower, $keyword) !== false) {
                return false;
            }
        }

        return !preg_match('/\d/', $value) && !preg_match('/^\s/', $value); // No digits and no leading space
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The name contains blocked content or is improperly formatted.';
    }
}
