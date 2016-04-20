<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FUKPackage extends Model {

    protected $connection = 'legacy';
    private $fillable = [
        'IFSDepot',
        'gateway',
        'docketno',
        'packageno',
        'pkgType',
        'pkgLength',
        'pkgWidth',
        'pkgHeight',
        'pkgWeight',
        'pkgVolWeight',
        'scanned',
        '1DBarcode',
        '2DBarcode',
        'tstamp',
        'scanlocn'
    ];

}
