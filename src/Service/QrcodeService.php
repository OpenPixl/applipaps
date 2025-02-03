<?php

namespace App\Service;

use App\Entity\Admin\Security\Employed;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Font\OpenSans;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\RequestStack;


class QrcodeService
{
    public function __construct(
        protected RequestStack $request,
    ){}

    public function qrcode_share_register($url, Employed $employed)
    {
        $objDateTime = new \DateTime('NOW');
        $dateString = $objDateTime->format('d-m-Y H:i:s');

        $path = dirname(__DIR__, 2).'/public/prescriptors/'.$employed->getFirstName().'_'.$employed->getLastName();

        // set qrcode
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(243, 244, 246, ),
            //logoPath: __DIR__.'/assets/symfony.png',
            //logoResizeToWidth: 50,
            //logoPunchoutBackground: true,
            //labelText: 'This is the label',
            //labelFont: new OpenSans(20),
            //labelAlignment: LabelAlignment::Center
        );

        $result = $builder->build();

        //generate name
        $namePng = 'qc-'.$employed->getFirstName().'-'.$employed->getLastName().'.png';

        if (is_dir($path)){
            //Save img png
            $result->saveToFile($path.'/'.$namePng);
        }else{
            // Création du répertoire s'il n'existe pas.
            mkdir($path, 0775, true);
            //Save img png
            $result->saveToFile($path.'/'.$namePng);
        }

        return $namePng;
    }
}