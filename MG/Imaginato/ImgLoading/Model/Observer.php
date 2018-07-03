<?php

class Imaginato_ImgLoading_Model_Observer
{

    public function renderImgcontainer($observer) {  
        $response = $observer->getEvent()->getControllerAction()->getResponse();
         
        $html = $response->getBody();  
        $result = [];
        preg_match_all('/<img[^>]+>/i',$html, $result);  
        $list_img = array_unique($result[0]);
        foreach($list_img as $old_img){
            $default_img = $old_img; 
            $dom = new DOMDocument();
            $dom->loadHTML($old_img);  
            $imgs = $dom->getElementsByTagName('img'); 
            $new_img = $old_img;
            $break = false; 
            foreach($imgs as $img){
                $width = preg_replace("/[^0-9]/", '', $img->getAttribute('width'));
                $height = preg_replace("/[^0-9]/", '', $img->getAttribute('height'));

                if($width && $height && $width > 1 && $height > 1){  
                    $percent_ratio = round(($height*100/$width),8);
                    $new_img = "<span class='wrap_image' style='padding-bottom:".$percent_ratio."%'>".$new_img."</span>";
                }
            }  
          
            $html =  str_replace($default_img, $new_img, $html);
        }  
        $response->setBody($html);
    } 
}
