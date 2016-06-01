<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/Developer/libs/Benchmark.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Benchmarking statistics
 */
namespace PHPPE;

class Benchmark
{
    static $file=".tmp/benchmarks";

/**
 * Clear benchmark data
 */
    static function clear()
    {
        @unlink(self::$file);
    }

/**
 * Collect benchmark statistics
 * @usage php public/index.php benchmark
 */
    static function stats()
    {
        //! get data
        $samples = @file(static::$file);
        if(empty($samples))
            return [];

        //! accumulate and aggregate
        $data=[];
        foreach($samples as $line) {
            $sample = json_decode($line, true);
            foreach($sample as $k=>$s) {
                //! skip baseline
                if(empty($s[0]))
                    continue;
                //! new elements
                if(!isset($data[$k])){
                    $data[$k]=[
                        'avg'=>$s[0],
                        'min'=>$s[0],
                        'max'=>$s[0],
                        'cnt'=>1
                    ];
                } else {
                    if($s[0]<$data[$k]['min'])
                        $data[$k]['min']=$s[0];
                    if($s[0]>$data[$k]['max'])
                        $data[$k]['max']=$s[0];
                    $data[$k]['avg']=sprintf("%.8f",($data[$k]['avg']*$data[$k]['cnt']+$s[0])/++$data[$k]['cnt']);
                }
            }
        }
        $s = 0;
        foreach($data as $k=>$v) {
            $data[$k]['str']=sprintf("%.8f", $s);
            $s+=$v['avg'];
        }
        return $data;
    }

}
