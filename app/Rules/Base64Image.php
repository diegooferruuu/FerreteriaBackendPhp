<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Validation\Concerns\ValidatesAttributes;
use Symfony\Component\HttpFoundation\File\File;

class Base64Image implements InvokableRule
{
    use ValidatesAttributes;
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if( !$this->validateImage( $attribute, $this->convertToFile($value) ) ) {
            $fail('validation.base64image')->translate([
                'atribute' => $attribute,
            ]);
        }
    }

    protected function convertToFile(string $value): File
    {
        if (strpos($value, ';base64') !== false) {
            [, $value] = explode(';', $value);
            [, $value] = explode(',', $value);
        }

        $binaryData = base64_decode($value);
        $tmpFile = tempnam(sys_get_temp_dir(), 'base64validator');
        file_put_contents($tmpFile, $binaryData);

        return new File($tmpFile);
    }
}
