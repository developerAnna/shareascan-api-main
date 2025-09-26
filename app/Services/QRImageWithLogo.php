<?php

namespace App\Services;

use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Common\EccLevel;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Pagination\LengthAwarePaginator;
use chillerlan\QRCode\Output\QRCodeOutputException;

class QRImageWithLogo extends QRGdImagePNG
{
    public function dump(string|null $file = null, string|null $logo = null): string
    {
        $logo ??= '';
        $this->options->returnResource = true;

        if ($logo && (!is_file($logo) || !is_readable($logo))) {
            throw new QRCodeOutputException('invalid logo');
        }

        parent::dump($file); // Generate the QR code first

        if ($logo) {
            // Add logo after generating QR code
            $im = imagecreatefrompng($logo);
            $w = imagesx($im);
            $h = imagesy($im);
            $lw = (($this->options->logoSpaceWidth - 2) * $this->options->scale);
            $lh = (($this->options->logoSpaceHeight - 2) * $this->options->scale);
            $ql = ($this->matrix->getSize() * $this->options->scale);
            imagecopyresampled($this->image, $im, (($ql - $lw) / 2), (($ql - $lh) / 2), 0, 0, $lw, $lh, $w, $h);
        }

        $imageData = $this->dumpImage();
        Storage::disk('public')->put($file, $imageData);

        return $imageData;
    }
}
