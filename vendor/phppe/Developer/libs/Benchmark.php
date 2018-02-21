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
        setlocale(LC_ALL, 'C');

        //! get data
        $samples = @file(static::$file);
        if(empty($samples))
            return [];

        //! accumulate and aggregate
        $data=[];
        foreach($samples as $line) {
            $sample = json_decode($line, true);
            $i=array_keys($sample)[0];
            foreach($sample[$i] as $k=>$s) {
                //! skip baseline
                if(empty($s[0]))
                    continue;
                //! new elements
                if(!isset($data[$i][$k])){
                    $data[$i][$k]=[
                        'avg'=>$s[0],
                        'min'=>$s[0],
                        'max'=>$s[0],
                        'cnt'=>1
                    ];
                } else {
                    if($s[0]<$data[$i][$k]['min'])
                        $data[$i][$k]['min']=$s[0];
                    if($s[0]>$data[$i][$k]['max'])
                        $data[$i][$k]['max']=$s[0];
                    $data[$i][$k]['avg']=sprintf("%.6f",($data[$i][$k]['avg']*$data[$i][$k]['cnt']+$s[0])/++$data[$i][$k]['cnt']);
                }
            }
        }
        foreach($data as $url=>$d) {
            $s = 0; $data[$url]['delta'] = $data[$url]['total'] = 0;
            foreach($d as $k=>$v) {
                if($k=="total"||$k=="delta"||$k=="count") continue;
                if($v['max']-$v['min']>$data[$url]['delta'])
                    $data[$url]['delta']=sprintf('%.6f',$v['max']-$v['min']);
                $data[$url]['total']+=$v['avg'];
                $data[$url][$k]['str']=sprintf("%.6f", $s);
                $s+=$v['avg'];
            }
        }
        $data[$url]['total']=sprintf("%.f",$data[$url]['total']);
        return $data;
    }

}
