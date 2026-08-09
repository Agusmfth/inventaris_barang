<?php
namespace App\Services;
use App\Models\Asset;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
class AssetQrCodeService
{
    public function publicUrl(Asset $asset):string{return route('assets.public-info',['asset'=>$asset->asset_code]);}
    public function svgDataUri(Asset $asset,int $size=260):string{return(new SvgWriter())->write($this->qr($asset,$size))->getDataUri();}
    public function png(Asset $asset,int $size=900):string{return(new PngWriter())->write($this->qr($asset,$size))->getString();}
    private function qr(Asset $asset,int $size):QrCode{return QrCode::create($this->publicUrl($asset))->setEncoding(new Encoding('UTF-8'))->setErrorCorrectionLevel(ErrorCorrectionLevel::Medium)->setSize($size)->setMargin(max(8,(int)round($size*.04)));}
}
