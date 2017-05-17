<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class WorkController extends Controller
{
    public function getIndex(){
        echo '<pre>';
        $colors = $this->getColors();

        $parts = [];
        $h = fopen(base_path('rebrickable_parts_10019-1-rebel-blockade-runner-ucs.csv'), 'r');
        fgetcsv($h);//ignore first line
        while($row = fgetcsv($h)){
            $row = array_combine(['id', 'color', 'quantity'], $row);
            $row['legoId'] = $row['id'] . sprintf("%02d", $colors[$row['color']]);
            $parts[] = $row;
        }

//        print_r($parts);

        echo '<table>';
        echo '<tr><th>ID</th><th>Color</th><th>LEGO ID</th><th>Qtt</th></tr>';
        foreach($parts as $part){
            echo '<tr>';
            echo "<td>{$part['id']}</td>";
            echo "<td>{$part['color']}</td>";
            echo "<td>{$part['legoId']}</td>";
            echo "<td>{$part['quantity']}</td>";
            echo '<td><img style="width: 20px;" src="https://sh-s7-live-s.legocdn.com/is/image/LEGOPCS/'.$part['legoId'].'_s1?$PABspin$"/></td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function getColors(){
        $colorsFile = base_path('colors.json');
        if( ! file_exists($colorsFile)){
            $client = new Client([
                'headers' => [
                    'Authorization' => 'key 8a1b334e129b8bc5332c8d60e0f37d17',
                ],
            ]);
            $response = $client->get('https://rebrickable.com/api/v3/lego/colors/');
            $colors = collect(json_decode($response->getBody(), true)['results'])
                ->map(function($v){
                    if(isset($v['external_ids']['LEGO'])){
                        $v['lego_id'] = $v['external_ids']['LEGO']['ext_ids'][0];
                        return $v;
                    }
                })->pluck('lego_id', 'id')->filter()->toArray();
            file_put_contents($colorsFile, json_encode($colors));
        }
        else{
            $colors = json_decode(file_get_contents($colorsFile), true);
        }

        //When no color
        $colors['9999'] = '';

        return $colors;
    }
}
