<?php
/**
 * Created by PhpStorm.
 * User: tonigurski
 * Date: 20.04.18
 * Time: 15:02
 */

namespace Sumpfpony\EntityHistoryBundle\Util;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class Dumper
{
    static function dump($item)
    {
        $htmlDumper = new HtmlDumper();
        $varCloner = new VarCloner();

        return $htmlDumper->dump($varCloner->cloneVar($item));
    }
}